<?php

namespace PMC\JW_YT_Video_Migration;

/**
* PMC_WP_CLI_Video_Migration | pmc-wp-cli-video-migration.php
*
* @author brandoncamenisch
* @version 2017-03-24 brandoncamenisch - PMCBA-202:
* - A WP-CLI that will migrate videos from JWPlayer into a format that YouTube can consume.
* Uses S3 to store the objects in transit.
*
* @NOTE: This should not rely on PMC Plugins in any way so that its portable.
*
**/

use \Buuum\S3 as S3;

use \JWPlayerApi\Api as JW;

use \Madcoda\Youtube as YT;

use \WP_CLI as WP_CLI;

use Services_Setup as SS;

\WP_CLI::add_command( 'pull-jw', 'PMC\JW_YT_Video_Migration\CLI_Video_Migration' );

/**
* CLI_Video_Migration
*
* @since 2017-03-24
*
* @version 2017-03-24 - brandoncamenisch - PMCBA-202:
* - A WP-CLI that will migrate videos from JWPlayer into a format that YouTube can consume.
* Uses S3 to store the objects in transit.
*
* @TODO: Write a method for comparing the spreadsheet with the API results to track down missing videos
* @TODO: Write a method for fix missing from spreadsheet
*
**/
class CLI_Video_Migration extends \WP_CLI_Command {

	public $assoc_args = array();

	public $setup = false;

	public $sheet;

	// Acts as rate limiter mostly helpful for JW requests ( probably don't need often though )
	public $sleep = 0;

	public $spreadsheet;

	public $jw;

	public $yt;

	public $s3;

	public $gd;

	// Used to store the initial /videos/list requests as a combined array of results
	protected $results = array();

	public function __construct( $args = array(), $assoc_args = array() ) {
		parent::__construct( $args, $assoc_args );
	}


	public function setup( $assoc_args ) {
		// Removes auth errors with WP-CLI outputting feedback for the commander
		session_start();

		// Setup the arguments as part of
		$this->set_args( $assoc_args );

		// Setup objects that connect with the APIs we need
		$this->yt_setup();
		$this->jw_setup();
		$this->s3_setup();
		$this->gd_setup();

		// If any of these return false then bail it should have failed already but just making sure
		if ( ! in_array( false, array( $this->yt, $this->jw, $this->s3, $this->gd ), true ) ) {
			WP_CLI::success( 'Success in setting up services' );
			return $this->setup = true;
		} else {
			WP_CLI::error( 'Failed at everything... Quitting! ¯\_(ツ)_/¯' );
		}
	}


	/**
	* Command to pull videos from JWPlayer API and move those to S3
	* @subcommand pullvids
	*
	* ## OPTIONS
	*
	* [--result-offset=<result-offset>
	* : The offset for pagination for the API /videos/list request.
	* With the offset not inclusive to the current offset 10 will
	* start at 11.
	* ---
	* default: 0
	* ---
	*
	* [--result-limit=<result-limit>
	* : A limit to the number of results returned from the API ( defaults-50/Max-1000 )
	* ---
	* default: 50
	* ---
	*
	* [--maxvids=<maxvids>]
	* : The maximum number of videos that we're willing to make requests on.
	*
	* [--sleep=<sleep>]
	* : If you want to sleep requests in between each video API batch ( in seconds )
	*
	* <argsfile>
	*
	* [--overwrite-s3]
	* : Overwrites the S3 videos with whatever new data we have from the API.
	*
	* [--overwrite-jw-api-list]
	* : Overwrites the saved API data for JW for the results list.
	*
	* [--vidkey=<vidkey>]
	* : If you only want to target a single video update pass in the jw key
	*
	* [--timeit]
	* : Times all video calls and the entire process.
	*
	* [--fix-missing]
	* : Only updates the keys that aren't in the spreadsheet from the API
	*
	* ## EXAMPLES
	*
	* Pull all jw vids save them to S3 along with any JSON, CSV file information
	* from the JW API and update the google spreadsheet.
	*
	* wp pull-jw pullvids --argsfile=somefile.json --timit --maxvids=10
	*
	*
	**/
	public function pull_vids( $args = array(), $assoc_args = array() ) {
		// This setups everything we need for most cli commands
		$this->setup( $assoc_args );

		// Start the timer
		$start_time = $this->timer( true, 0 );

		if ( $this->setup ) {
			// This will pull a list of all videos and store it as a property $this->results
			$this->pull_video_list();
			// After we have a list we need to request each video from that list and store the info
			$this->move_video();
		} else {
			WP_CLI::error( 'Failed at pulling and moving videos... Quitting!' );
		}
		// Return the total time it took for all requests
		$this->timer( false, $start_time );
	}


	/**
	* @NOTE: This method is only used to gauge a metric on the possibility of the
	* video being uploaded onto YouTube. This is in no way intended to be used as
	* a hard piece of data to determine an action on any asset being moved around.
	* This method adds two array key => values for `yturl` and `ytvidid` used for
	* Gathering data on possible duplicate YouTube videos.
	**/
	public function sort_dupes( $yt_arr ) {

		$youtube = new YT( array(
			'key' => $this->assoc_args['ytapikey'],
		) );

		// Set Default Parameters
		$params = array(
			'q'             => $yt_arr['title'] . ' ' . $yt_arr['description'],
			'type'          => 'video',
			'part'          => 'id, snippet',
			'channelId'     => $yt_arr['channel'],
			'order'         => 'title',
			'maxResults'    => 5,
		);

		// Make Intial Call. With second argument to reveal page info such as page tokens.
		$search = $youtube->searchAdvanced( $params, true );

		if ( is_array( $search['results'] ) ) {
			WP_CLI::warning( "Found {$search['info']['totalResults']} results for JW Key {$yt_arr['custom-id']}" );
			$yt_arr['dupe'] = $search['info']['totalResults'];
			// @NOTE: we set the key here to 0 because we're only worried about the top result
			$url = "https://www.youtube.com/watch?v={$search['results'][0]->id->videoId}";
			$yt_arr['yturl'] = $url;
			$yt_arr['ytvidid'] = $search['results'][0]->id->videoId;
			WP_CLI::line( "Setting YouTube URL in spreadsheet to {$url}" );
		}

		return $yt_arr;
	}


	/**
	* Command to pull videos from S3 that are categorized as not being on YouTube
	* @subcommand pullnondupes
	*
	* ## OPTIONS
	*
	* <argsfile>
	*
	* [--timeit]
	* : Times all video calls and the entire process.
	*
	* ## EXAMPLES
	*
	* This command doesn't have a lot of arguments
	* wp pull-jw pullnondupes --timeit --argsfile=somefile.json
	*
	**/
	public function pull_s3_non_dupes( $args = array(), $assoc_args = array() ) {
		$this->setup( $assoc_args );

		// Start the timer
		$start_time = $this->timer( true, 0 );

		// If any of these return false then bail it should have failed already but just making sure
		if ( $this->setup ) {
			WP_CLI::success( 'Success in setting up services' );
			// Get the items from the spreadsheet
			if ( isset( $this->assoc_args['vidkey'] ) ){
				$items = $this->sheet->select( array(
					'custom-id' => $this->assoc_args['vidkey'],
				) );
			} else {
				$items = $this->sheet->select( array(
					'dupe' => '',
				) );
			}

			$command = shell_exec( 'type -p aws' );
			// @NOTE: this piece of logic will only work on EC2 obviously
			if ( is_array( $items ) && '/usr/bin/aws' === trim( $command ) ) {
				WP_CLI::success( 'Found ' . count( $items ) . ' in the spreadsheet.' );
				foreach ( $items as $key => $val ) {
					// long but necessary
					echo shell_exec( "aws s3 cp s3://{$this->assoc_args['sbucket']}/{$this->assoc_args['sbucketdir']}/{$items[ $key ][ 'custom-id' ]}/ {$this->assoc_args['s3localvids']}{$items[ $key ]['custom-id']} --recursive --exclude '*' --include '{$items[ $key ]['custom-id']}.csv' --include '{$items[ $key ]['filename']}'" );
				}
				WP_CLI::success( 'Moved all videos from S3 to EC2. Validate your results.' );
			} else {
				WP_CLI::error( 'Either the items list is bad or the aws command does not exist on this server.' );
			}
		} else {
			WP_CLI::error( 'Failed at pulling s3 non dupes... Quitting!' );
		}

		$this->timer( false, $start_time );
	}


	/**
	* This will update the google spreadsheet with the mappings for JW to YouTube
	* from the delivery.complete XML files that YouTube creates after a video has
	* been submitted for processing.
	*
	* @subcommand upspreadmap
	*
	* ## OPTIONS
	*
	* <argsfile>
	*
	* [--timeit]
	* : Times all video calls and the entire process.
	*
	* ## EXAMPLES
	*
	* This command doesn't have a lot of arguments
	* wp pull-jw upspreadmap --timeit --argsfile=somefile.json
	*
	**/
	public function update_spreadsheet_mapping( $args = array(), $assoc_args = array() ) {
		$this->setup( $assoc_args );

		// Start the timer
		$start_time = $this->timer( true, 0 );

		// If any of these return false then bail it should have failed already but just making sure
		if ( $this->setup ) {
			// Find files inside our videos directories and create an array of that.
			$it = new \RecursiveDirectoryIterator( $this->assoc_args['s3localvids'] );

			WP_CLI::line( "Finding files in {$this->assoc_args['s3localvids']}" );
			// The file extension we expect back from YouTube should be XML
			$display = 'xml';

			foreach ( new \RecursiveIteratorIterator( $it ) as $file ) {
				if ( file_exists( $file ) && $display === pathinfo( $file, PATHINFO_EXTENSION ) ) {
					WP_CLI::line( "Found file {$file} parsing for information..." );
					$xml = file_get_contents( $file );
					$xml = simplexml_load_string( $xml );
					// Loop through the xml document
					foreach ( $xml->action as $struct ) {
						// Loop through and update the spreadsheet
						if ( 'Process file' == $struct->attributes()->name && 'csv' !== pathinfo( $struct->filename, PATHINFO_EXTENSION ) ) {
							$base = substr( $struct->filename, 0, strpos( $struct->filename, "_" ) );
							foreach ( $struct as $act ) {
								$item = $this->sheet->select( array(
								'custom-id' => $base,
								) );

								if ( 'Submit video' == $act->attributes()->name && 'Success' == $act->status ) {
									if ( is_array( $item ) ) {
										WP_CLI::line( "Found key match for JW->YT {$item} setting YouTube Video Id to {$act->id}" );
										$this->sheet->update(
										key( $item ),
										'ytvidid',
										(string) $act->id
										);
										$this->sheet->update(
											key( $item ),
											'yturl',
											(string) "https://www.youtube.com/watch?v={$act->id}"
										);
										/**
										* Rename the file to act as a sort of indicator that the video has been processed so we don't have
										* to do it again for larger batchs.
										**/
										rename( $file, "$file.done" );
									} else {
										WP_CLI::line( "The item {$act->id} does not exist in the spreadsheet" );
									}
								} elseif ( 'Submit video' == $act->attributes()->name && 'Failure' == $act->status ) {
									WP_CLI::line( $act->status_detail );
									$this->sheet->update(
									key( $item ),
									'dupe',
									(string) $act->status_detail
									);
								}
							}
						}
					}
				}
			}
		} else {
			WP_CLI::error( 'Failed at updating spreadsheet mapping or no files found' );
		}
		$this->timer( false, $start_time );
	}


	/**
	* This will take the final results from the spreadsheet and pull that into an
	* array which can be used to create and save the final array mapping option 
	*
	* @subcommand upmap
	*
	* ## OPTIONS
	*
	* <argsfile>
	*
	* [--timeit]
	* : Times all video calls and the entire process.
	*
	* ## EXAMPLES
	*
	* This command doesn't have a lot of arguments
	*
	* wp pull-jw upmap --timeit --argsfile=somefile.json
	*
	**/
	public function update_wp_mapping( $args = array(), $assoc_args = array() ) {
		$this->setup( $assoc_args );
		// Start the timer
		$start_time = $this->timer( true, 0 );
		$map = array();
		// Get the spreadsheet information
		if ( is_array( $this->sheet->items ) ) {
			// Loop through the results creating key value mapping
			foreach ( $this->sheet->items as $key => $val ) {
				// Update the value that will be in the google spreadsheet
				$map[$this->sheet->items[$key]['custom-id']] = $this->sheet->items[$key]['ytvidid'];
			}
		}

		// This is only useful in porting the option onto a site.
		if ( is_array( $map ) ) {
			update_option( 'pmc_mapping_temp_option_name', $map, 'true' );
			WP_CLI::line( print_r( json_encode( $map ) ) );
		}

	}


	/**
	* set_args | pmc-wp-cli-video-migration.php
	*
	* @since 2017-03-24 - Sets Args
	*
	* @uses WP_CLI
	*
	* @author brandoncamenisch
	* @version 2017-03-24 - PMCBA-202:
	* - Parses the command arguments for a file and sets more arguments
	* as a property with sensitive information in regards to API
	* credentials and other information.
	*
	* @param assoc_args array
	* @return array|bool Returns an array of property assoc_args
	**/
	public function set_args( $assoc_args ) {
		if ( $this->check_file( $assoc_args['argsfile'] ) ) {
			WP_CLI::success( "Args file {$assoc_args['argsfile']} exists. Setting arguments for migration..." );
			$this->decode_args( $assoc_args );
		} else {
			WP_CLI::error( "Args file {$assoc_args['argsfile']} is not a file or file does not exist please set --argsfile in the command arguments" );
		}
	}


	/**
	* timer | pmc-wp-cli-video-migration.php
	*
	* @since 2017-03-24 - Times the total execution time as well as the
	* execution time for each video request.
	*
	* @uses WP_CLI
	*
	* @author brandoncamenisch
	* @version 2017-03-24 - PMCBA-202
	* - Times the total execution time as well as the
	* execution time for each video request.
	*
	* @param start boolean
	* @param start_time int the time this instance of the timer started
	* @return int|false in microtime or false on failure.
	**/
	public function timer( $start = true, $start_time = 0 ) {
		if ( $this->assoc_args['timeit'] && $start ) {
			WP_CLI::line( str_repeat( '=', 50 ) . '> Starting timer ⏱' );
			return microtime( true );
		} elseif ( $this->assoc_args['timeit'] && ! $start ) {
			$end_time = microtime( true );
			$execution_time = ( $end_time - $start_time ) / 60;
			WP_CLI::line( str_repeat( '=', 50 ) . "> Total Execution Time: {$execution_time}" );
		} else {
			return false;
		}
	}


	/**
	* yt_setup | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10 - Sets up YT info
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - PMCBA-363:
	* - This will setup the YT and connect to the YT API with information
	* from the argsfile.
	*
	* @return this->yt
	**/
	public function yt_setup() {
		if ( isset( $this->assoc_args['ytapikey'] ) ) {
			WP_CLI::line( 'Setup the YouTube API' );
			$yt = new YT( array(
				'key' => $this->assoc_args['ytapikey'],
			) );
			if ( $yt instanceof YT ) {
				WP_CLI::success( 'Setup YouTube API.' );
				return $this->yt = $yt;
			} else {
				WP_CLI::line( print_r( var_dump( $yt ) ) );
				WP_CLI::error( 'Could not create a valid instance of the YT API' );
			}
		} else {
			WP_CLI::error( 'You need to set a YouTube API key in your argsfile as ytapikey' );
		}
	}


	/**
	* jw_setup | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10 - Sets up JW info
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - PMCBA-363:
	* - This will setup the JW and connect to the JW API with information
	* from the argsfile.
	*
	* @return this->jw
	**/
	public function jw_setup() {
		WP_CLI::line( 'Attempting to build the JWPlayer API credentials' );
		try {
			$this->jw = new JW( $this->assoc_args['jwkey'], $this->assoc_args['jwsecret'] );
		} catch ( Exception $e ) {
			WP_CLI::warning( 'Build of JW API credentials failed from class' );
			return false;
		} finally {
			if ( $this->jw instanceof JW ) {
				WP_CLI::success( 'Build of JW API credentials' );
				return $this->jw;
			} else {
				WP_CLI::error( 'Build of JW API credentials failed to create instance' );
			}
		}
	}


	/**
	* s3_setup | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10 - Sets up S3 info
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - PMCBA-363:
	* - This will setup the S3 and connect to the S3 API with information
	* from the argsfile.
	*
	* @return this->s3
	**/
	public function s3_setup() {
		try {
			WP_CLI::line( 'Attempting to setup S3 API details' );
			S3::setAuth( $this->assoc_args['skey'], $this->assoc_args['ssecret'] );
			S3::setBucket( $this->assoc_args['sbucket'] );
		} catch ( Exception $e ) {
			WP_CLI::error( 'There was an error setting S3 credentials and/or bucket' );
		} finally {
			if ( S3::hasAuth() ) {
				WP_CLI::success( 'API details for S3 setup' );
				return $this->s3 = true;
			} else {
				WP_CLI::error( 'Build of S3 credentials failed' );
			}
		}
	}


	/**
	* gd_setup | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10 - Sets up GD info
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - PMCBA-363:
	* - This will setup the GD class and connect to the GD API with information
	* from the argsfile.
	*
	* @return this->gd
	**/
	public function gd_setup() {
		// Auth
		WP_CLI::line( "Setting credentials from file {$this->assoc_args['gdauth']}" );
		if ( file_exists( $this->assoc_args['gdauth'] ) && is_readable( $this->assoc_args['gdauth'] ) ) {
			WP_CLI::success( "Set credentials from file {$this->assoc_args['gdauth']} for Google" );
		} else {
			WP_CLI::error( "Setting the creds from file {$this->assoc_args['gdauth']} failed file does not exist" );
		}

		// Auth client
		try {
			WP_CLI::line( 'Attempting to get client from google' );
			$client = \Google_Spreadsheet::getClient( $this->assoc_args['gdauth'] );
			$client->config( array(
				'cache' => false,
				'cache_dir' => 'cache',
				'cache_expires' => 1,
			) );
		} catch ( Exception $e ) {
			WP_CLI::error_multi_line( $e );
			WP_CLI::error( 'Failed setting up spreadsheet client info.' );
		} finally {
			if ( $client instanceof \Google_Spreadsheet_Client ) {
				WP_CLI::success( 'Google client fetched' );
				$this->spreadsheet = $this->set_spreadsheet( $client );
				$this->sheet = $this->set_sheet();
				return $this->gd = $client;
			} else {
				WP_CLI::error( 'Failed fetching client from Google' );
			}
		}
	}


	/**
	* move_video | pmc-wp-cli-video-migration.php
	*
	* @since 2017-03-24 - Requests and updates video information.
	*
	* @author brandoncamenisch
	* @version 2017-03-24 - PMCBA-202:
	* - Pings the JWPlayer API for a single video using a list of videos to grab
	* from the $list. It then calls one too many methods to loop through the
	* videos to grab header information, update S3 objects, and write to the
	* google spreadsheet document.
	*
	* @return null
	*
	**/
	public function move_video() {
		// Video iterations count starting at 1
		$video_it_count = 1;

		// Not the most efficient way to do this but it works and time is of the essence.
		if ( isset( $this->assoc_args['vidkey'] ) ) {
			foreach ( $this->results as $key => $val ) {
				if ( $this->results[$key]['key'] !== $this->assoc_args['vidkey'] ) {
					unset( $this->results[$key] );
				}
			}
		}

		// This will loop through the spreadsheet items and remove anything that is already in the spreadsheet
		if ( isset( $this->assoc_args['fix-missing'] ) ) {
			// Get all API data
			$items_arr = array();
			// Get the items from the spreadsheet
			foreach ( $this->sheet->items as $key => $val ) {
				// Set the custom key array
				$items_arr[] = $this->sheet->items[ $key ]['custom-id'];
			}

			// Unset keys that aren't in the spreadsheet that are in the API results
			foreach ( $this->results as $key => $val ) {
				if ( in_array( $this->results[ $key ]['key'], $items_arr ) ) {
					unset( $this->results[ $key ] );
				}
			}
		}

		// If there's a max vids then we need remove some items from the end of the
		// array to make sure we get the counts right and are trimming the fat.
		if ( isset( $this->assoc_args['maxvids'] ) ) {
			$video_total = count( array_slice( $this->results, 0, $this->assoc_args['maxvids'] ) );
			$this->results = array_slice( $this->results, 0, $this->assoc_args['maxvids'] );
		} else {
			$video_total = count( $this->results );
		}

		do {
			foreach ( $this->results as $key => $val ) {
				// Start the timer
				$start_time = $this->timer( true, 0 );
				WP_CLI::line( "Video count {$video_it_count}/{$video_total}" );
				// Setup the vars to pass
				$vid_key  = $this->results[ $key ]['key'];
				// Set a json object in S3 for the original video information
				$this->s3_json_update( json_encode( $this->results[ $key ] ), $vid_key, '-orig.json' );
				// Gets the conversions returns false if failed
				$video = $this->get_set_video_conversions( $vid_key, $this->results[ $key ] );
				if ( $video ) {
					$this->s3_json_update( json_encode( $video ), $vid_key, '-modified.json' );
					$this->s3_videos_update( $video, $vid_key );
					$yt_arr = $this->map_yt_array( $video );
					$this->s3_csv_update( $yt_arr, $video['key'] );
					$this->sheet_update( $this->sheet, $video, $yt_arr );
				} else {
					WP_CLI::error( 'Moving the JW header conversions failed' );
				}
				// Sleep if passed as arg. This acts as a sort of rate limiter.
				if ( $this->assoc_args['sleep'] >= 1 ) {
					WP_CLI::line( "Sleeping for {$this->assoc_args['sleep']} second 😴" );
					sleep( $this->assoc_args['sleep'] );
				}
				// Return the total time it took for this request
				$this->timer( false, $start_time );
				$video_it_count++;
			}
		} while ( $video_it_count <= $video_total );
	}


	/**
	* s3_csv_update | pmc-wp-cli-video-migration.php
	*
	* @since 2017-03-27
	*
	* @author brandoncamenisch
	* @version 2017-03-27 - feature/PMCBA-202:
	* - Takes an array of data and creates a csv file on S3 for YouTube to consume when uploading videos
	*
	* @param data array of values for the video we want YouTube to consume
	* @param vid_key the video key that we use from JW to determine the video mapping
	* @return void
	*
	**/
	public function s3_csv_update( $data, $vid_key ) {
		$filename = "{$this->assoc_args['sbucketdir']}/{$vid_key}/{$vid_key}.csv";

		// We don't want to mess with filesystems so write to memory
		$fh = fopen( 'php://temp', 'rw' );
		$csv_keys = array();
		// Replace the array keys because the _ doesn't work with caching for some reason in the Google Spreadsheet.
		foreach ( array_keys( $data ) as $key ) {
			$csv_keys[] = str_replace( '-', '_', $key );
		}

		// Put the headers
		fputcsv( $fh, $csv_keys );
		// Put the row of formatted data
		fputcsv( $fh, $data );
		rewind( $fh );
		// Remove any unwanted characters
		$csv = $this->untexturize( stream_get_contents( $fh ) ); // This gets rid of unwanted fancy stuff
		fclose( $fh );

		WP_CLI::line( $csv );

		$response = S3::putObjectString( $csv,$filename );
		if ( 200 === $response['code'] ) {
			WP_CLI::success( "CSV inserted for {$vid_key} with filename {$filename} and S3 URL {$response['url']['default']}" );
		} else {
			WP_CLI::line( print_r( $response ) );
			WP_CLI::error( "CSV PUT request failed for {$vid_key} with filename {$filename} response was" );
		}
	}


	/**
	* s3_videos_update | pmc-wp-cli-video-migration.php
	*
	* @since 2017-03-27
	*
	* @author brandoncamenisch
	* @version 2017-03-27 - feature/PMCBA-202:
	* - Takes the video file URL and sends the upload via URL from JW.
	*
	* @param video array of video conversions to loop through
	* @param vid_key string of the JW player video reference key
	*
	**/
	public function s3_videos_update( $video, $vid_key ) {
		// Loop through the conversions to get the URL and the video key to send a request to S3
		foreach ( $video['conversions'] as $conv ) {
			// If there is an error message from the JW API then we can't process the video
			if ( ! is_null( $video['error'] ) ) {
				continue;
			}

			if ( ! isset( $conv['s3filename'] ) ) {
				WP_CLI::line( print_r( $conv ) );
				WP_CLI::error( "There is no filename for video for key {$vid_key} {$conv['key']}. Halting..." );
				return false;
			}

			$file_url = $this->assoc_args['s3url'] . $conv['s3filename'];

			$response = $this->s3_url_exists( $file_url );

			if ( $response ) {
				WP_CLI::warning( "File {$conv['s3filename']} already exists on S3 at {$file_url} Skipping insertion." );
			} elseif ( isset( $conv['link'] ) && isset( $conv['s3filename'] ) ) {
				WP_CLI::line( "Sending video with primary key {$vid_key} and video key {$conv['key']} from original link {$conv['link']['full']} saving as S3 filename {$conv['s3filename']}" );
				$response = S3::putObjectUrl( $conv['headers']['Location'], $conv['s3filename'] );
				if ( 200 === $response['code'] ) {
					WP_CLI::success( "Video PUT request successful for video with key {$vid_key} and URL {$response['url']['default']}" );
				} else {
					WP_CLI::line( print_r( $response ) );
					WP_CLI::warning( "Video PUT request failed for video filename {$conv['s3filename']} response was ^" );
				}
			}
		}
		WP_CLI::success( "All video requests sent for {$vid_key}" );
		return true;
	}


	/**
	* s3_json_update | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10 
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - PMCBA-363:
	* - Method takes json API data for a video from JW and sends it off to S3 for
	* storage.
	*
	* @param json json ( php ) data from jw api
	* @param vid_key string of the video key from jw
	* @param ext string of the extension that we're checking
	* @return bool true/false
	**/
	public function s3_json_update( $json, $vid_key, $ext ) {

		if ( ! is_string( $json ) && ! is_array( $json ) ) {
			WP_CLI::error( 'That is not a valid json object. Aborting!' );
			WP_CLI::line( print_r( $json ) );
			return false;
		}

		$json_filename = "{$this->assoc_args['sbucketdir']}/{$vid_key}/{$vid_key}{$ext}";
		$json_url = $this->assoc_args['s3url'] . $json_filename;

		$response = $this->s3_url_exists( $json_url );

		if ( $response ) {
			WP_CLI::warning( "JSON already exists on S3 for video id {$vid_key} for URL {$json_url}. Skipping..." );
			return true;
		} else {
			WP_CLI::line( "Sending json with id {$vid_key}" );
			$response = S3::putObjectString( $json, $json_filename, array(
				'Content-Type' => 'text/plain',
			) );

			if ( 200 === $response['code'] ) {
				WP_CLI::success( "JSON inserted for {$vid_key} with filename {$json_filename} and S3 URL {$response['url']['default']}" );
			} else {
				WP_CLI::error( "JSON PUT request failed for {$vid_key} with json filename {$json_filename} response was" );
				WP_CLI::error( print_r( $response ) );
				return false;
			}
		}

	}


	/**
	* get_set_video_conversions | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10 
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - PMCBA-363:
	* - Gets conversion data from the JW API and Sets it as an altered array.
	*
	* @param documents a single argument of a function or method.
	* @return documents the return value of functions or methods.
	**/
	public function get_set_video_conversions( $vid_key, $video ) {
		// Call the API for the video conversion list
		$conversions = $this->jw->call( '/videos/conversions/list', array(
			'api_format' => 'php',
			'video_key' => (string) $vid_key,
		) );

		if (
			is_array( $conversions )
			&& is_array( $video )
			&& 'ok' === $conversions['status']
		) {
			// Merge the conversion into the video array
			$video = array_merge( $video, $conversions );
			// Now that we have the video conversion we need to grab their headers
			$video = $this->get_set_headers( $vid_key, $video );
			// Return the video array
			return $video;
		} else {
			WP_CLI::line( print_r( $video ) );
			WP_CLI::line( print_r( $conversions ) );
			WP_CLI::error( 'The conversions and video arrays could not merge' );
			return false;
		}
	}


	/**
	* get_set_headers | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10 
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - master:
	* - Gets API data from JW and request the headers for our videos so that they
	* can be sent off to S3 to be saved. @NOTE that the headers will expire after
	* some time.
	*
	* @param documents a single argument of a function or method.
	* @return documents the return value of functions or methods.
	**/
	public function get_set_headers( $vid_key, $video ) {
		if ( ! is_array( $video ) && ! isset( $video['conversions'] ) ) {
			WP_CLI::line( print_r( $video ) );
			WP_CLI::error( 'The video object is not correct to use for conversions links' );
			return false;
		}
		foreach ( $video['conversions'] as $key => $val ) {
			if ( ! is_null( $video['error'] ) || 'Failed' === $video['conversions'][ $key ]['status'] || ! is_null( $video['conversions'][ $key ]['error'] ) ) {
				WP_CLI::warning( "There is an error with the video id {$vid_key}. Either conversion failed or mediatype is not a video {$videos['conversions'][ $key ]['mediatype']} Skipping..." );
				unset( $video['conversions'][ $key ] );
			} else {
				if ( strpos( $video['conversions'][ $key ]['link']['path'], 'originals' ) ) {
					// For some reason the original URL link is under /videos and not /originals
					//@example: https://content.jwplatform.com/videos/qkxgvNk3.mp4
					$video['conversions'][ $key ]['link']['full'] = str_replace( 'originals', 'videos', 'http://content.jwplatform.com' . $video['conversions'][ $key ]['link']['path'] );
				} else {
					$video['conversions'][ $key ]['link']['full'] = "http://content.jwplatform.com{$video['conversions'][$key]['link']['path']}";
				}

				$base_extension = pathinfo( $video['conversions'][ $key ]['link']['full'], PATHINFO_EXTENSION );

				$base_filename = sanitize_file_name( "{$vid_key}_{$video['conversions'][$key]['key']}_{$video['conversions'][$key]['template']['name']}.{$base_extension}" );
				// @NOTE: There should be no reason to get the mimme type for this filename.
				// Because all audio/video files are stored as mp4 files.
				$video['conversions'][ $key ]['s3filename'] = trailingslashit( $this->assoc_args['sbucketdir'] ) . trailingslashit( $vid_key ) . $base_filename;
				// Just to make things easy set the filename for the csv to consume
				$video['conversions'][ $key ]['ytfilename'] = $base_filename;

				if ( filter_var( $video['conversions'][ $key ]['link']['full'], FILTER_VALIDATE_URL ) === false ) {
					WP_CLI::warning( "{$video['conversions'][$key]['link']['full']} is not a valid URL for video key {$vid_key}" );
				} else {
					// We need the header location because some of the videos are redirected if not all of them.
					WP_CLI::line( "Fetching header location for video id {$vid_key} with key {$video['conversions'][$key]['key']}" );
					$video['conversions'][ $key ]['headers'] = get_headers( $video['conversions'][ $key ]['link']['full'], 1 );

					if ( $video['conversions'][ $key ]['headers'] && isset( $video['conversions'][ $key ]['headers']['Location'] ) ) {
						WP_CLI::success( "Fetched header location ( {$video['conversions'][$key]['headers']['Location']} ) for video id {$vid_key} from {$video['conversions'][$key]['link']['full']}" );
					} else {
						WP_CLI::line( print_r( $video['conversions'][ $key ]['headers'] ) );
						WP_CLI::warning( "Failed to fetch header location for video id {$vid_key} from {$video['conversions'][$key]['link']['full']}" );
					}
				}
			}
		}

		return $video;

	}


	/**
	* sheet_update | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Checks the spreadsheet for already existing row and if none exist then it
	* will insert a new row with identical information to that in the S3 CSV file
	*
	* @param sheet object of google spreadsheet API class
	* @param video array of video information in its unaltered state
	* @param yt_arr array of yt data that is also sent to S3 to save as CSV
	*
	* @return bool true/false
	**/
	public function sheet_update( $sheet, $video, $yt_arr ) {
		// Get the items from the spreadsheet
		$items = $sheet->select( array(
			'custom-id' => $video['key'],
		) );

		if ( ! empty( $items ) && count( $items ) > 1 ) {
			WP_CLI::error( "You have too many conflicting keys set in the custom-id column look at the spreadsheet ( {$this->assoc_args['gdspreadsheeturl']} ) for custom-id {$video['key']} this should match the JWPlayer video key and only be one" );
		} elseif ( ! empty( $items ) && count( $items ) === 1 ) {
			WP_CLI::warning( "Video information already exists in spreadsheet ( {$this->assoc_args['gdspreadsheeturl']} ) skipping {$video['key']}" );
			return false;
		} else {
			$yt_arr = $this->sort_dupes( $yt_arr );
			$sheet->insert( $yt_arr );
			WP_CLI::success( "Inserted row with custom-id {$video['key']}" );
			return true;
		}
	}


	/**
	* map_yt_array | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Maps data from the video array into a YouTube format.
	*
	* @param video array of video data to be mapped
	* @return yt_arr array of mapped data
	**/
	public function map_yt_array( $video ) {
		/**
		* An array of key value pairs that match YouTube final delivery format keys
		* and matches them to JWPlayer values ( values ). Do not re-order array the
		* google docs spreadheet depends on them. @Note: That header columns with a
		* dash ( - ) should be replaced with an underscore prior to import into YT 
		* The API for google drive won't accept an underscore ( _ ) for some reason 
		* and just skips those columns.
		**/
		$yt_arr = array(
			'filename'                  => 's3filename',
			'channel'                   => '',
			'custom-id'                 => 'key',
			'add-asset-labels'          => '', // @NOTE: This is good to use for grouping the videos to be sorted later
			'title'                     => 'title',
			'description'               => 'description',
			'keywords'                  => 'tags|one|two|three|four',
			'spoken-language'           => 'EN',
			'caption-file'              => '',
			'caption-language'          => 'EN',
			'category'                  => 'Entertainment',
			'privacy'                   => 'unlisted',
			'notify-subscribers'        => 'No',
			'start-time'                => '',
			'end-time'                  => '',
			'custom-thumbnail'          => '',
			'ownership'                 => 'US',
			'block-outside-ownership'   => 'No',
			'usage-policy'              => '',
			'enable-content-id'         => 'No',
			'reference-exclusions'      => '',
			'match-policy'              => '',
			'ad-types'                  => '',
			'ad-break-times'            => '',
			'playlist-id'               => '',
			'require-paid-subscription' => '',
		);

		foreach ( $yt_arr as $key => $val ) {
			switch ( $key ) {
				case 'filename':
					$file_name = array();
					foreach ( $video['conversions'] as $vkey => $vval ) {
						/**
						* We only want the video in its original format on YouTube. We can
						* theoretically update the asset in the future with any other video
						* since its attached to its unique jw_key:content_id on YT.
						* Config can be set in the argsfile -
						* `original`, `aac-audio`, `h.264-320px`, `h.264-480px`,`h.264-720px`, `h.264-1280px`, `h.264-1920px`
						**/
						$file_name[ $vkey ] = trim( sanitize_file_name( strtolower( $video['conversions'][ $vkey ]['template']['name'] ) ) );
					}

					if ( in_array( $this->assoc_args['videotype'], $file_name, true ) ) {
						$vkey = array_search( $this->assoc_args['videotype'], $filename );
						$yt_arr[ $key ] = (string) $video['conversions'][ $vkey ]['ytfilename'];
						break;
					} elseif ( in_array( 'h.264-1920px', $file_name, true ) ) {
						$vkey = array_search( 'h.264-1920px', $file_name );
						$yt_arr[ $key ] = (string) $video['conversions'][ $vkey ]['ytfilename'];
						break;
					} elseif ( in_array( 'h.264-1280px', $file_name, true ) ) {
						$vkey = array_search( 'h.264-1280px', $file_name );
						$yt_arr[ $key ] = (string) $video['conversions'][ $vkey ]['ytfilename'];
						break;
					} elseif ( in_array( 'h.264-720px', $file_name, true ) ) {
						$vkey = array_search( 'h.264-720px', $file_name );
						$yt_arr[ $key ] = (string) $video['conversions'][ $vkey ]['ytfilename'];
						break;
					} elseif ( in_array( 'original', $file_name, true ) ) {
						$vkey = array_search( 'original', $file_name );
						$yt_arr[ $key ] = (string) $video['conversions'][ $vkey ]['ytfilename'];
						break;
					} elseif ( in_array( 'h.264-480px', $file_name, true ) ) {
						$vkey = array_search( 'h.264-480px', $file_name );
						$yt_arr[ $key ] = (string) $video['conversions'][ $vkey ]['ytfilename'];
						break;
					} elseif ( in_array( 'h.264-320px', $file_name, true ) ) {
						$vkey = array_search( 'h.264-320px', $file_name );
						$yt_arr[ $key ] = (string) $video['conversions'][ $vkey ]['ytfilename'];
						break;
					} elseif ( ! is_null( $video['error'] ) ) {
						$yt_arr[ $key ] = $video['error']['message'];
						break;
					}
					break;
				case 'channel':
						$yt_arr[ $key ] = (string) $this->assoc_args['ytchannel'];
					break;
				case 'custom-id':
					$yt_arr[ $key ] = (string) $video['key'];
					break;
				case 'title':
					$yt_arr[ $key ] = (string) substr( $video['title'], 0, 100 ); // YT Title limit
					break;
				case 'description':
					$yt_arr[ $key ] = str_replace( '/,/', '', $video['description'] );
					break;
				case 'keywords':
					$yt_arr[ $key ] = (string) $this->sort_out_tags( $video );
					break;
				case 'add-asset-labels':
					$yt_arr[ $key ] = (string) $this->assoc_args['ytasslabel'];
					break;
			}
		}

		WP_CLI::success( 'Inserting row for YT data delivery on this video to:' );

		WP_CLI::line( print_r( $yt_arr ) );

		return $yt_arr;

	}


	/**
	* sort_out_tags | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10 
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Takes an array of tags with a `,` seperated format and converts it into a
	* `|` pipe seperated format.
	*
	* @param video string of `,` seperated tags 
	* @return keywords string of `|` seperated tags to be used as keywords on YT
	**/
	public function sort_out_tags( $video ) {
		//Loop through tags and replace , with |
		if ( isset( $video['tags'] ) ) {
			$tags = explode( ',', $video['tags'] );
			$keywords = '';
			foreach ( $tags as $tag ) {
				if ( $tag === end( $tags ) ) {
					$keywords .= trim( $tag );
				} else {
					$keywords .= trim( $tag ) . '|';
				}
			}
			return $keywords;
		} else {
			return false;
		}
	}


	/**
	* pull_video_list | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Makes an API request for a list of all videos from the JW API. Subsequent
	* API requests will be cached VIA S3 as a json stored object. The videos list
	* results are combined into a large array of ALL combined videos. This method
	* will also count the number of results and output that into the CLI script.
	*
	* @return this->results array of API request results as a comprehensive array
	**/
	public function pull_video_list() {
		$offset_while = (int) $this->assoc_args['result-offset'];
		$date = date( 'Y-m-d' );
		$list_filename = "{$this->assoc_args['sbucketdir']}/_queries/{$date}-offset{$this->assoc_args['result-offset']}-limit{$this->assoc_args['result-limit']}-results.json";

		WP_CLI::line( "Checking if file exists on S3 {$list_filename}" );
		$response = S3::getObject( $list_filename, $this->assoc_args['sbucket'] );
		if ( 200 === $response['code'] && true !== $this->assoc_args['overwrite-jw-api-list'] ) {
			WP_CLI::success( "File {$list_filename} exists on S3 we will use that for our video list" );
			return $this->results = json_decode( $response['message'], true );
		} elseif ( true === $this->assoc_args['overwrite-jw-api-list'] ) {
			WP_CLI::warning( 'You chose to overwrite the JW API credentials. Building from scratch...' );
		} else {
			WP_CLI::warning( "File {$list_filename} does not exist on S3. Calling JW API" );
		}

		do {
			WP_CLI::line( '' );
			try {
				WP_CLI::line( "Connecting to the JWPlayer API to retrieve video list offset of {$offset_while} and a result limit of {$this->assoc_args['result-limit']}" );
				$list = $this->jw->call( '/videos/list',
					array(
						'api_format' => 'php',
						'result_offset' => (int) $offset_while,
						'result_limit' => (int) $this->assoc_args['result-limit'],
					)
				);
			} catch ( Exception $e ) {
				WP_CLI::error_multi_line( $e );
				WP_CLI::error( 'JWPlayerApi failed to retrieve any valid results' );
			} finally {
				if ( $list && is_array( $list ) && $list['status'] === 'ok' ) {
					$count = count( $list['videos'] );
					$out_of = $count + $offset_while;
					WP_CLI::success( "Retrieved {$count} video(s) {$out_of}/{$list['total']} results" );
					$this->combine_results( $list['videos'] );
					$offset_while = $offset_while + $this->assoc_args['result-limit'];
				} else {
					WP_CLI::error( 'T JWPlayerApi failed to retrieve the any valid results' );
				}
			}
		} while ( $offset_while <= $list['total'] );

		WP_CLI::line( 'Total Results found ' . $list['total'] );
		WP_CLI::line( 'Result offset ' . $this->assoc_args['result-offset'] );
		if ( count( $this->results ) !== ( $list['total'] - $this->assoc_args['result-offset'] ) ) {
			WP_CLI::warning( 'The result video counts do not match' );
		}

		$response = S3::putObjectString( json_encode( $this->results ), $list_filename, array(
			'Content-Type' => 'text/plain',
		) );

		if ( 200 === $response['code'] ) {
			WP_CLI::success( "API list results saved on S3 as {$list_filename}" );
		} else {
			WP_CLI::line( print_r( $response ) );
			WP_CLI::error( "Failed to save api data json on S3 as {$list_filename}" );
		}

		return $this->results;
	}


	/**
	* combine_results | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10 
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Helper method for combining the JW API video/list results.
	*
	* @param list array of videos from API
	* @return this->results array list merged into combined results
	**/
	public function combine_results( $list ) {
		if ( is_array( $list ) && is_array( $this->results ) ) {
			return $this->results = array_merge( $this->results, $list );
		} else {
			return false;
		}
	}




	/**
	* set_spreadsheet | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10 
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Sets up the spreadsheet object and connection with google spreadsheet API
	*
	* @param gd object of spreadsheet that holds API information on connecting
	* @return file the for google spreadsheets
	**/
	public function set_spreadsheet( $gd ) {
		// Set the spreadsheet
		try {
			WP_CLI::line( 'Setting the spreadsheet in Google' );
			// Get the file by file ID
			$file = $gd->file( $this->assoc_args['gdspreadsheet'] );
		} catch ( Exception $e ) {
			WP_CLI::error( 'Setting the Google file failed' );
			return false;
		} finally {
			if ( $file instanceof \Google_Spreadsheet_File ) {
				WP_CLI::success( "Google spreadsheet set to {$this->assoc_args['gdspreadsheet']}" );
				return $file;
			} else {
				WP_CLI::error( 'Failed setting spreadsheet file on Google' );
				return false;
			}
		}
	}



	/**
	* set_sheet | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Sets up the sheet object and connection with google sheet API
	*
	* @return sheet_name the name of the sheet we're working with in spreadsheets
	**/
	public function set_sheet() {
		try {
			WP_CLI::line( "Setting/retrieving {$this->assoc_args['gdspreadsheet']} sheet to {$this->assoc_args['gdsheet']}" );
			$sheet_name = $this->spreadsheet->sheet( $this->assoc_args['gdsheet'] );
		} catch ( Exception $e ) {
			WP_CLI::error( "Failed setting/retrieving {$this->assoc_args['gdspreadsheet']} sheet to {$this->assoc_args['gdsheet']}" );
			return false;
		} finally {
			if ( $sheet_name instanceof \Google_Spreadsheet_Sheet ) {
				WP_CLI::success( "Spreadsheet {$this->assoc_args['gdspreadsheet']} sheet to {$this->assoc_args['gdsheet']} set" );
				return $sheet_name;
			} else {
				WP_CLI::error( "Failed setting/retrieving {$this->assoc_args['gdspreadsheet']} sheet to {$this->assoc_args['gdsheet']}" );
				return false;
			}
		}
	}


	/**
	* Convert fancy quotes/dashes to normal quotes/dashes
	*
	* @param string $text Text string in which fancy quotes/dashes etc are to be converted
	* @param string $type The type in which fancy quotes/dashes etc are to be converted, ie., normal text or HTML. Defaults to Text.
	*
	* @since 2012-08-29 Amit Gupta
	* @version 2013-10-04 Amit Gupta - added '&quot;' to conversion list in text mode in untexturize()
	**/
	public static function untexturize( $text, $type = 'text' ) {
		if ( empty( $text ) ) {
			return $text;
		}

		//type can be either HTML or TEXT
		$type = ( strtolower( $type ) == 'html' ) ? 'html' : 'text';

		$utf8_find = array(
			"\xe2\x80\x98", // single left curved quote
			"\xe2\x80\x99", // single right curved quote
			"\xe2\x80\x9c", // double left curved quote
			"\xe2\x80\x9d", // double right curved quote
			"\xe2\x80\x93", // endash
			"\xe2\x80\x94", // emdash
			"\xe2\x80\xa6", // ellipsis
		);
		$char_find = array( chr( 145 ), chr( 146 ), chr( 147 ), chr( 148 ), chr( 150 ), chr( 151 ), chr( 133 ) );

		$text_replace = array( "'", "'", '"', '"', '-', '--', '...' );
		if ( 'html' === $type ) {
			$text_replace = array( "'", "'", '"', '"', '&ndash;', '&mdash;', '&hellip;' );
		}

		//do uft-8 replace
		$text = str_replace( $utf8_find, $text_replace, $text );

		//do char replace
		$text = str_replace( $char_find, $text_replace, $text );

		if ( 'text' === $type ) {
			$text = str_replace( '&nbsp;', ' ', $text );	//convert html char for space
			$text = str_replace( array( '&#8216;', '&#8217;', '&lsquo;', '&rsquo;', '&#x2019;' ), "'", $text );	//convert html entity for single quotes and apostrophe

			$text = html_entity_decode( $text );	//convert html entities to text
		}

		return $text;
	}



	/**
	* s3_url_exists | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10 
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Checks if a resource on S3 might exist by checking the headers for a good
	* response from the url. With that we can assume that the S3 object probably
	* already exists on S3.
	*
	* @param url string of url to check on S3
	* @return bool true/false
	**/
	public function s3_url_exists( $url ) {
		if ( $this->assoc_args['overwrite-s3'] ) {
			return false;
		}

		$headers = get_headers( $url );
		if ( strpos( $headers[0],'200' ) ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	* check_file | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10 
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Checks if file exists and is readable.
	*
	* @param file string of file path
	* @return bool true/false
	**/
	public function check_file( $file ) {
		if ( isset( $file ) && file_exists( $file ) && is_readable( $file ) ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	* decode_args | pmc-wp-cli-video-migration.php
	*
	* @since 2017-04-10
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Decodes properties from the specified json configuration file.
	*
	* @param assoc_args array of command line args from WP-CLI
	* @return assoc_args array of modified arguments which might be sensitive
	**/
	public function decode_args( $assoc_args ) {
		// Decode the args file which should be a json file
		WP_CLI::line( "Decoding args file {$assoc_args['argsfile']}..." );
		// Attempt to decode the json
		$args = json_decode( file_get_contents( $assoc_args['argsfile'] ), true );
		if ( is_array( $args ) && ! empty( $args ) ) {
			foreach ( $args as $key => $val ) {
				$assoc_args[ $key ] = $val;
			}
			WP_CLI::success( 'Args set into command arguments' );
			return $this->assoc_args = $assoc_args;
		} else {
			WP_CLI::error( "Decoding or args file {$assoc_args['argsfile']} failed check your json formatting" );
		}
	}

}

// EOF
