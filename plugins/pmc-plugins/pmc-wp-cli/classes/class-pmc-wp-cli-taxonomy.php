<?php

/**
 * Class PMC_WP_CLI_Taxonomy
 *
 * Taxonomy-related commands for WP-CLI
 *
 * ex. `wp pmc-taxonomy export --tax=category,post_tag`
 */
class PMC_WP_CLI_Taxonomy extends PMC_WP_CLI_Base {



	/**
	 * Intake a CSV of vertical/category pairs from two columns FROM, TO
	 * and search all specified posts type that match the FROM vertical/category
	 * to re-assign a new value TO vertical/category pair.
	 *
	 * ## OPTIONS
	 *
	 * --csv-file=<csv-file>
	 * : Comma-delimited file with column name FROM, TO: vertical/category
	 *
	 * [--post-type=<types>]
	 * : Comma-delimited string of post types, default: post.
	 *
	 * [--start-date=<yyyy-mm-dd>]
	 * : Start date
	 *
	 * [--end-date=<yyyy-mm-dd>]
	 * : End date
	 *
	 * [--batch-size=<batch-size>]
	 * : The WP_Query posts_per_page argument you wish to set (number of batches)
	 *
	 * [--sleep=<sleep>]
	 * : The number of seconds to sleep between each batch
	 *
	 * [--dry-run]
	 * : No operations are carried out while in this mode
	 *
	 * [--need-confirm]
	 * : Prompt for confirmation before begin
	 *
	 * [--disable-es-index]
	 * : Disable ES Indexing
	 *
	 * [--log-file=<log-file>]
	 * : Path/Filename to the log file
	 *
	 * [--post-status=<types>]
	 * : Comma-delimited string of post status, default: publish.
	 *
	 * [--should-redirect]
	 * : Whether to add redirect rules or not, default: true.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pmc-taxonomy convert-vertical-category
	 *
	 * @subcommand convert-vertical-category
	 */
	public function convert_vertical_category( $args, $assoc_args ) {
		// keep code in own separate file to not cluster this main class
		if ( file_exists( dirname( __DIR__ ) . '/taxonomy/convert-vertical-category.php' ) ) {
			require_once( dirname( __DIR__ ) . '/taxonomy/convert-vertical-category.php' );
			(new \PMC\WPCLI\Taxonomy\ConvertVerticalCategory( $args, $assoc_args ))->run();
		}
	}

	/**
	 * Will delete taxonomy terms that are not assign to any posts
	 *
	 * ## OPTIONS
	 *
	 * --taxonomy=<taxonomy>
	 * : Comma-delimited string of taxonomy
	 *
	 * [--batch-size=<batch-size>]
	 * : The WP_Query posts_per_page argument you wish to set (number of batches)
	 *
	 * [--sleep=<sleep>]
	 * : The number of seconds to sleep between each batch
	 *
	 * [--dry-run]
	 * : No operations are carried out while in this mode
	 *
	 * [--need-confirm]
	 * : Prompt for confirmation before begin
	 *
	 * [--batch-paged]
	 * : The WP_Query page to start on. Helpful for picking up where you left off.
	 *
	 * [--batch-max-paged]
	 * : The maximum number of wp_query pages to iterate through. Helpful for running a small test batch.
	 *
	 * [--log-file=<log-file>]
	 * : Path/Filename to the log file
	 *
	 * ## EXAMPLES
	 *
	 *     wp pmc-taxonomy delete-empty
	 *
	 * @subcommand delete-empty
	 */
	public function delete_empty( $args, $assoc_args ) {
		// keep code in own separate file to not cluster this main class
		if ( file_exists( dirname( __DIR__ ) . '/taxonomy/delete-empty.php' ) ) {
			require_once( dirname( __DIR__ ) . '/taxonomy/delete-empty.php' );
			(new \PMC\WPCLI\Taxonomy\DeleteEmpty( $args, $assoc_args ))->run();
		}
	}

	/**
	 * Export the output of get_terms() as a CSV.
	 *
	 * @author Corey Gilmore
	 *
	 * @see https://wordpressvip.zendesk.com/requests/43819
	 * @version 2015-08-18 Corey Gilmore PPT-5027
	 *
	 * @synopsis [--tax=<comma_delimited_string>] [--export-csv=<file>]
	 * @subcommand export
	 */
	public function export_terms( $args = array(), $assoc_args = array() ) {
		WP_CLI::line('Starting...');

		if ( ! empty( $assoc_args['export-csv'] ) ) {
			$csv_file = $assoc_args['export-csv'];
		}

		if( !is_writable( $csv_file ) ) {
			WP_CLI::error( "Unable to open CSV export file '$csv_file' for writing." );
		}
		if( file_exists( $csv_file ) ) {
			WP_CLI::confirm( "CSV export file '$csv_file' already exists, overwrite it?" );
		}

		if ( ! empty( $assoc_args['tax'] ) ) {
			$taxonomies = $assoc_args['tax'];
		}
		WP_CLI::line( "Writing to CSV export file " . $csv_file . "" );

		$taxonomies = explode( ',', str_replace( ' ', '', $taxonomies ) );

		$bad_tax = false;
		// Make sure the taxonomy exists
		foreach( $taxonomies as $tax ) {
			if( !taxonomy_exists( $tax ) ) {
				WP_CLI::warning( 'Invalid taxonomy: ' . sanitize_title_with_dashes( $tax ) );
				$bad_tax = true;
			}
		}
		if( $bad_tax ) {
			WP_CLI::error( 'Invalid taxonomy specified.' );
		}

		$gt_args = array(
			'hide_empty'        => false,
			'childless'         => true,
			'orderby'           => 'count',
			'order'             => 'DESC',
			'cache_domain'      => __FUNCTION__,
		);

		$terms = get_terms( $taxonomies, $gt_args );

		if( !$fp = fopen(  $csv_file, 'w' ) ) {
			WP_CLI::error( "Unable to open CSV export file '" . $csv_file . "' for writing." );
		}

		foreach( $terms as $term ) {
			fputcsv( $fp, (array)$term );
		}
		fclose($fp);

		WP_CLI::success( sprintf( 'Complete. Wrote %s terms to %s', sizeof( $terms ), $csv_file ) );
	}

	/**
	 * Create numerous CSV files (per post type) which audit
	 * current category & vertical usage on the given site.
	 *
	 * ## OPTIONS
	 *
	 * [--batch-size=<batch-size>]
	 * : The WP_Query posts_per_page argument you wish to set (number of batches)
	 *
	 * [--sleep=<sleep>]
	 * : The number of seconds to sleep between each batch
	 *
	 * --disallowed-cpts=<disallowed-cpts>
	 * : Don't find these post types. Comma-delimited string.
	 * e.g. on wwd we know runway-review posts use verticals and categories
	 * but their url structure does not follow the same post v/c structure
	 *
	 * --csv-dir=<csv-dir>
	 * : The absolute directory path where to write csv output files.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pmc-taxonomy export_vertical_category_usage --url=wwd.vip.local --disallowed-cpts=runway-review,runway-fashion-scoop,runway-backstage,runway-video,runway-party,runway-schedule --csv-dir=/srv/wwd-data --batch-size=100 --sleep=0
	 *
	 * @synopsis --disallowed-cpts=<disallowed-cpts> --csv-dir=<csv-dir> [--batch-size=<batch-size>] [--sleep=<sleep>]
	 */
	function export_vertical_category_usage( $args, $assoc_args ) {

		// Extract common args sets up class internal vars for
		// common args like --dry-run, --batch-size, --sleep, etc.
		$this->_extract_common_args( $assoc_args );

		$csv_output = array();

		$csv_dir = ! empty( $assoc_args['csv-dir'] ) ? $assoc_args['csv-dir'] : '' ;

		// Define/create the directories where we'll write
		// the csv files containing the data we create
		$csv_dir = trailingslashit( sprintf(
			'%s%s-vc-usage-%s',
			trailingslashit( $csv_dir ),
			get_bloginfo( 'name' ),
			date( 'Y-m-j-H-i', time() )
		) );

		if ( ! is_dir( $csv_dir ) ) {
			mkdir( $csv_dir, 0755, true );
		} else {
			if ( ! is_writable( $csv_dir ) ) {
				WP_CLI::error( sanitize_text_field( "$csv_dir is not a writable directory" ) );
			}
		}

		$disallowed_cpts = ! empty( $assoc_args['disallowed-cpts'] ) ? $assoc_args['disallowed-cpts'] : array() ;

		$disallowed_cpts = explode( ',', $disallowed_cpts );

		if ( ! is_array( $disallowed_cpts ) && empty( $disallowed_cpts ) ) {
			WP_CLI::error( '--disallowed_cpts must contain a comma-delimited string' );
		}

		// Build an array of post types which use both verticals and categories
		$tmp_post_types = $post_types = array();

		foreach ( array( 'vertical', 'category' ) as $taxonomy ){
			$taxonomy_data = get_taxonomy( $taxonomy );

			if ( false !== $taxonomy_data ) {
				$tmp_post_types = array_merge( $tmp_post_types, $taxonomy_data->object_type );
			}
		}

		$tmp_post_types = array_unique( $tmp_post_types );

		// Remove cpts in disallowed list
		$tmp_post_types = array_diff( $tmp_post_types, $disallowed_cpts );

		// Create an array where each cpt is an index
		// we'll use this array to store output data for
		// each post type
		foreach ( $tmp_post_types as $key => $post_type ) {
			$post_types[ $post_type ] = array();
		}

		unset( $taxonomy_data, $tmp_post_types );

		// Display a table of the post types for the given taxonomy
		WP_CLI\Utils\format_items(
			'table',
			array_map( function( $item ) {
				return array( "post types:" => $item );
			}, array_keys( $post_types ) ),
			array( "post types:" )
		);

		// Select all the posts in the site within our $post_types
		$this->batch_wp_query_task_runner(

			// WP_Query arguments
			array(
				'post_type' => array_keys( $post_types ),

				// This is a one-off query that does not need to create caches
				// we might as well bypass those—every little bit helps with
				// this big query.
				'cache_results' => false,
			),

			// Callback function applied to every post returned via WP_Query
			function( $post ) use ( &$post_types, $csv_dir, &$csv_output ) {

				// Extract information from the post's URL
				// e.g. for a post with a permalink like:
				// http://wwd2.vip.local/retail-news/people/macys-promotes-jeff-gennette-to-president-7625562/
				// $permalink_end will contain the portion after the site url, e.g.
				// retail-news/people/macys-promotes-jeff-gennette-to-president-7625562/
				//
				// $permalink_start will contain the portion before the post_name, e.g.
				// retail-news/people
				//
				// $permalink_vc will be an array where..
				// [0] = retail-news
				// [1] = people
				$permalink       = get_permalink( $post->ID );
				$permalink_end   = str_replace( get_bloginfo( 'url' ), '', $permalink );
				$permalink_start = str_replace( $post->post_name, '', $permalink_end );
				$permalink_start = trim( $permalink_start, '/' );
				$permalink_vc    = explode( '/', $permalink_start );

				// Log all the unique vertical/category patterns
				if ( count( $permalink_vc ) >= 2 ) {
					$post_types[ $post->post_type ]['vc-patterns'][ trim( $permalink_start, '/' ) ][] = $post->ID;
				}

				// Create an array of taxonomies and their terms
				// for the purpose of this script we only need the
				// vertical and category taxonomies
				$taxonomies = array(
					'vertical' => wp_list_pluck( wp_get_post_terms( $post->ID, 'vertical' ), 'slug' ),
					'category' => wp_list_pluck( wp_get_post_terms( $post->ID, 'category' ), 'slug' ),
				);

				$improper_url = false;
				$improper_url_error = array();
				$i = 0;

				// Loop through each taxonomy and create sub-arrays
				// on $post_types as needed for posts with missing terms,
				// multiple terms, and posts with improper url structures.
				foreach( $taxonomies as $taxonomy => $terms ) {

					if ( is_array( $terms ) && ! empty( $terms ) ) {

						// Log any instances where there are multiple terms for the current taxonomy
						if ( count( $terms ) > 1 ) {
							$post_types[ $post->post_type ]['multiple-terms'][ $taxonomy ][] = array(
								'ID'         => $post->ID,
								'permalink'  => $permalink,
								'term_count' => count( $terms ),
								'terms'      => implode( ', ', $terms ),
							);
						}

						// Log any items who's first category or vertical is not used in the URL
						if ( count( $permalink_vc ) >= 2 ) {
							if ( $permalink_vc[ $i ] !== $terms[0] ) {
								$improper_url = true;
								$improper_url_error[] = $taxonomy;
							}
						}

					} else {

						// Log any instances where there is no vertical term
						$post_types[ $post->post_type ]['missing-terms'][ $taxonomy ][] = array(
							'ID' => $post->ID,
							'permalink' => $permalink,
						);
					}

					$i++;
				}

				// Log any instances where the post has an improper URL
				// e.g. first vertical or category not used in the URL
				if ( $improper_url ) {
					$post_types[ $post->post_type ]['improper-urls'][] = array(
						'ID'         => $post->ID,
						'error'      => implode( ', ', $improper_url_error ),
						'permalink'  => $permalink,
						'v/c'        => $permalink_start,
						'verticals'  => implode( ', ', $taxonomies['vertical'] ),
						'categories' => implode( ', ', $taxonomies['category'] ),
					);
				}

				// Increment the progress bar and our internal batch counter of posts processed
				$this->batch_update_progress_bar();

				// Write/Append CSV files at the end of each batch
				// This prevents our logging array ($post_types) from
				// growing exponentially out of control when logging
				// data for 200k+ posts.
				if ( $this->is_end_of_batch() ) {

					foreach ( $post_types as $post_type => $data ) {

						// Some CSV's are written per taxonomy
						foreach ( $taxonomies as $taxonomy => $terms ) {

							// CSV of posts without multiple vertical or category term(s)
							$csv_filename = $this->batch_increment_csv(
								array( 'ID', 'URL', 'TERM COUNT', 'TERMS' ),
								$post_types[ $post_type ]['multiple-terms'][ $taxonomy ],
								$csv_dir . "{$post_type}-posts-multiple-$taxonomy-terms.csv",
								$this->batch_num_posts_processed,
								$this->batch_size,
								$this->batch_found_posts
							);

							// Clear out the items which have already been written
							unset( $post_types[ $post_type ]['multiple-terms'][ $taxonomy ] );

							if ( false !== $csv_filename ) {
								$csv_output[ $csv_filename ] = array(
									'success' => 'Created ' . $csv_filename,
								);
							}

							// CSV of posts with multiple vertical or category terms
							$csv_filename = $this->batch_increment_csv(
								array( 'ID', 'URL' ),
								$post_types[ $post_type ]['missing-terms'][ $taxonomy ],
								$csv_dir . "{$post_type}-posts-no-$taxonomy.csv",
								$this->batch_num_posts_processed,
								$this->batch_size,
								$this->batch_found_posts
							);

							// Clear out the items which have already been written
							unset( $post_types[ $post_type ]['missing-terms'][ $taxonomy ] );

							if ( false !== $csv_filename ) {
								$csv_output[ $csv_filename ] = array(
									'success' => 'Created ' . $csv_filename,
								);
							}
						}

						// CSV of posts with improper URLs
						$csv_filename = $this->batch_increment_csv(
							array( 'ID', 'error', 'permalink', 'v/c', 'verticals', 'categories' ),
							$post_types[ $post_type ]['improper-urls'],
							$csv_dir . "{$post_type}-improper-vertical-category-structures.csv",
							$this->batch_num_posts_processed + 1,
							$this->batch_size,
							$this->batch_found_posts
						);

						// Clear out the items which have already been written
						unset( $post_types[ $post_type ]['improper-urls'] );

						if ( false !== $csv_filename ) {
							$csv_output[ $csv_filename ] = array(
								'success' => 'Created ' . $csv_filename,
							);
						}
					}
				}

				// Don't let these reused var's memory footprint
				// expand on each iteration.
				unset( $permalink, $permalink_start, $permalink_end, $permalink_vc, $improper_url, $improper_url_error, $post_verticals, $post_categories, $i, $post, $taxonomies, $csv_filename );
			}
		);

		// The array of vertical/category patterns keeps counts of all posts
		// matching shared patterns. Due to this we're unable to write this CSV
		// incrementally above like the other CSV files.
		if ( is_array( $post_types ) && ! empty( $post_types ) ) {

			foreach ( $post_types as $post_type => $data ) {

				if ( ! empty( $data['vc-patterns'] ) && is_array( $data['vc-patterns'] ) ) {

					ksort( $data['vc-patterns'] );

					$csv_filename = $this->write_to_csv(
						$csv_dir . "$post_type-vertical-category-structures.csv",
						array( 'PATH', 'COUNT', 'POST IDS' ),
						$data['vc-patterns'],

						// We need to modify each csv row slightly
						// $value will be an array of posts, but we'll
						// return an array with the count of those items
						// and a command-delimited string of the post ids
						function ( $key, $value ) {
							return array(
								$key,
								count( $value ),
								implode( ', ', $value ),
							);
						}
					);

					if ( false !== $csv_filename ) {
						$csv_output[ $csv_filename ] = array(
							'success' => 'Created ' . $csv_filename,
						);
					}
				}
			}
		}

		// Send csv file creation status to stdout
		if ( ! empty( $csv_output ) && is_array( $csv_output ) ) {
			foreach( $csv_output as $filename => $messages ) {
				foreach ( $messages as $status => $message ) {
					switch ( $status ) {
						case 'success' : WP_CLI::success( sanitize_text_field( $message ) ); break;
						case 'error'   : WP_CLI::error( sanitize_text_field( $message ), false ); break;
						case 'line'    : WP_CLI::line( sanitize_text_field( $message ) ); break;
					}
				}
			}
		}
	}

	/**
	 * When a post, which follows the vertical/category URL structure,
	 * has multiple terms in it's vertical or category taxonomies,
	 * this script will remove the terms not in the URL
	 *
	 * Overall process:
	 * + Fetch all posts in the given post types
	 * + Loop through each post
	 * + Fetch the post's vertical and/or category terms
	 * + Identify which vertical and/or category terms are not used in the post's URL
	 * + Remove those terms
	 * + After all posts have been processed, recount term relationships for the terms removed
	 *
	 * ## OPTIONS
	 *
	 * [--batch-size=<batch-size>]
	 * : The WP_Query posts_per_page argument you wish to set (number of batches)
	 *
	 * [--sleep=<sleep>]
	 * : The number of seconds to sleep between each batch
	 *
	 * [--dry-run]
	 * : No operations are carried out while in this mode
	 *
	 * [--batch-paged]
	 * : The WP_Query page to start on. Helpful for picking up where you left off.
	 *
	 * [--batch-max-paged]
	 * : The maximum number of wp_query pages to iterate through. Helpful for running a small test batch.
	 *
	 * [--log-file=<log-file>]
	 * : Path/Filename to the log file
	 *
	 * [--csv-dir=<csv-dir>]
	 * : The absolute directory path where to write csv output files.
	 *
	 * --post_types=<post_types>
	 * : Only process posts in these post_types. Comma-delimited string.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pmc-taxonomy unrelate_vc_terms_from_posts_not_found_the_in_posts_url --url=wwd2.vip.local --post_types=post,tout,package,list,pmc-gallery --batch-size=500 --sleep=0 --csv-dir=/srv/www --log-file=/srv/www/wwd-data.log --dry-run --batch-paged=4 --batch-max-paged=6
	 *
	 * @synopsis --post_types=<post_types> [--batch-size=<batch-size>] [--sleep=<sleep>] [--csv-dir=<csv-dir>] --log-file=<log-file> [--dry-run] [--batch-paged] [--batch-max-paged]
	 */
	function unrelate_vc_terms_from_posts_not_found_the_in_posts_url( $args, $assoc_args ) {

		global $wpdb;

		// Extract common args sets up class internal vars for
		// common args like --dry-run, --batch-size, --sleep, etc.
		$this->_extract_common_args( $assoc_args );

		if ( $this->dry_run ) {
			$this->_write_log( 'Doing Dry Run..' );
		} else {
			WP_CLI::confirm( 'ALERT!! — This is a destructive operation!! Do you wish to proceed?' );
		}

		// Only proceed if there were post types given
		if ( empty( $assoc_args['post_types'] ) ) {
			$this->_write_log( 'Must specify at least one post type', true );
		}

		// Only proceed if a CSV ourput directory was given
		if ( empty( $assoc_args['csv-dir'] ) ) {
			$this->_write_log( 'Must specify a directory to write CSV output', true );
		}

		// Define the post types, csv dir, and full path to the csv file
		$post_types = explode( ',', sanitize_text_field( $assoc_args['post_types'] ) );
		$csv_dir = untrailingslashit( sanitize_text_field( $assoc_args['csv-dir'] ) );
		$csv = "$csv_dir/remove_vc_terms_not_in_post_urls.csv";

		// Prompt the user to proceed
		$this->_write_log( 'Searching through posts of type: ' . implode( ', ', $post_types ) . ' ..' );
		$this->_write_log( "Searching posts for multiple vertical and category terms .." ) ;
		$this->_write_log( "Writing CSV log file to $csv .." );
		$this->_write_log( "Writing detailed log file to $this->log_file .." );
		$this->_write_log( "--------------------------" );
		WP_CLI::confirm( 'Proceed?' );

		// Create the CSV directory if it does not exist
		if ( ! is_dir( $csv_dir ) ) {
			mkdir( $csv_dir, 0755, true );
		}

		// Bail if the CSV dir is not accessible
		if ( ! is_writable( $csv_dir ) ) {
			$this->_write_log( "$csv_dir is not a writable directory", true );
		}

		// Write the output csv file header columns
		$csv_filename = $this->write_to_csv(
			$csv,
			array(),
			array( array(
				'ID',
				'POST TYPE',
				'PERMALINK',
				'VERTICALS',
				'COUNT OF VERTICAL TERMS TO REMOVE',
				'VERTICALS TO REMOVE',
				'VERTICALS TO REMOVE IDS',
				'CATEGORIES',
				'COUNT OF CATEGORY TERMS TO REMOVE',
				'CATEGORIES TO REMOVE',
				'CATEGORIES TO REMOVE IDS',
			) ),
			null,
			'w'
		);

		// The following vars will be used to log information
		// about each post that's processed by our script
		$count_of_terms_removed = $dryrun_count_of_terms_removed = array( 'vertical' => 0, 'category' => 0 );
		$count_of_posts_w_multiple_terms = 0;
		$count_of_posts_wo_multiple_terms = 0;
		$count_of_posts_wo_vc_structure = 0;
		$count_these_term_relationships = array( 'vertical' => array(), 'category' => array() );

		// Run batch wp_query command
		// Select all the posts in the site within our $post_types
		$this->batch_wp_query_task_runner(

			// WP_Query arguments
			array(
				'post_type'   => $post_types,
				'post_status' => 'any',
				'fields'      => 'ids',
			),

			// Callback function applied to every post returned via WP_Query
			function( $post_id ) use ( &$wpdb, $csv, &$count_of_terms_removed, &$dryrun_count_of_terms_removed, &$count_of_posts_w_multiple_terms, &$count_of_posts_wo_multiple_terms, &$count_of_posts_wo_vc_structure, &$count_these_term_relationships ) {

				$this->_write_log( "Processing post $this->batch_num_posts_processed of $this->batch_found_posts", false, true );
				$this->_write_log( "Post ID: $post_id", false, true );

				// e.g. site.com/vertical-term/category-term/post-slug
				// e.g. site.com/vertical-term/category-term/gallery/post-slug
				$permalink = get_permalink( $post_id );

				$this->_write_log( "Permalink: $permalink", false, true );

				// For some reason (didn't dig into it) get_permalink()
				// fails on galleries, and returns a URL like:
				// http://wwd2.vip.local/?post_type=pmc-gallery&p=78840
				// But we're expecting something like:
				// http://wwd2.vip.local/vert-hey/cat-cow/gallery/derek-lam-rtw-fall-2014-78840/
				// However, in this case get_sample_permalink() seems to work
				if ( false !== strpos( $permalink, '?post_type' ) ) {

					$permalink = get_sample_permalink( $post_id );

					if ( ! empty( $permalink ) && is_array( $permalink ) ) {
						$permalink = str_replace( '%pagename%', $permalink[1], $permalink[0] );

						$this->_write_log( "The permalink doesn't look correct.. trying another..", false, true );
						$this->_write_log( "Permalink: $permalink", false, true );
					}
				}

				// Remove the site URL from the permalink
				// e.g. /vertical-term/category-term/post-slug
				// e.g. /vertical-term/category-term/gallery/post-slug
				$permalink_end = str_replace( get_bloginfo( 'url' ), '', $permalink );

				// Remove the post name off the end of the path
				// e.g. /vertical-term/category-term/
				// e.g. /vertical-term/category-term/gallery
				$permalink_path = str_replace( basename( $permalink ), '', $permalink_end );

				// Strip off the leading/trailing forward slashes
				// e.g. vertical-term/category-term
				// e.g. vertical-term/category-term/gallery
				$permalink_path = trim( $permalink_path, '/' );

				// Remove the gallery slug if this is a gallery post
				$permalink_path = str_replace( 'gallery', '', $permalink_path );

				$this->_write_log( "Permalink path: $permalink_path", false, true );

				// Convert the remaining permalink path into an array
				// e.g. array( 'vertical-term', 'category-term' )
				$permalink_path_pieces = explode( '/', $permalink_path );

				// Ensure all the forward slashes are gone (trim()'s failed on me before..)
				$permalink_path_pieces = array_map(
					function ( $value ) {
						return str_replace( '/', '', $value );
					}, $permalink_path_pieces
				);

				// Ensure there are no empty array items
				// e.g. $permalink_path_pieces = array( 'vertical-term', 'category-term' )
				$permalink_path_pieces = array_filter( $permalink_path_pieces );

				// Only proceed if the post has a clear vertical and a clear category
				if ( count( $permalink_path_pieces ) < 2 ) {
					$count_of_posts_wo_vc_structure++;
					$this->_write_log( "Skipping - This post's url does not utilize the vertical/category structure", false, true );
					$this->_write_log( "--------------------------", false, true );
					$this->batch_update_progress_bar();
					return;
				}

				$this->_write_log( 'Permalink path pieces: ' . implode( ', ', $permalink_path_pieces ), false, true );
				$post_terms = array(
					'vertical' => array(
						'terms' => array(),
					),
					'category' => array(
						'terms' => array(),
					),
				);

				// Loop through and grab the post's category and/or vertical terms
				foreach ( $post_terms as $taxonomy => $terms_data ) {

					$terms = get_the_terms( $post_id, $taxonomy );
					if ( ! empty( $terms ) && is_array( $terms ) ) {

						$this->_write_log( "$taxonomy terms: " . implode( ', ', wp_list_pluck( $terms, 'slug' ) ), false, true );

						foreach ( $terms as $term ) {

							// Do our empty and type checks here so we don't have to do them again later
							if ( ! empty( $term ) && is_a( $term, 'WP_Term' ) ) {
								$post_terms[ $taxonomy ]['terms'][] = $term;
							}
						}
					}
					unset( $terms );
				}

				// Only proceed if there are multiple terms in at least one of the taxonomies
				if ( count( $post_terms['vertical']['terms'] ) <= 1 && count( $post_terms['category']['terms'] ) <= 1 ) {
					$count_of_posts_wo_multiple_terms++;

					if ( count( $post_terms['vertical'] ) <= 1 ) {
						$this->_write_log( "This post does not have multiple vertical terms ..", false, true );
					}
					if ( count( $post_terms['category'] ) <= 1 ) {
						$this->_write_log( "This post does not have multiple category terms ..", false, true );
					}

					$this->_write_log( "...Skipping", false, true );
					$this->_write_log( "--------------------------", false, true );
					$this->batch_update_progress_bar();
					return;
				}

				$count_of_posts_w_multiple_terms++;

				// Fetching the post type is only for the logging
				$post_type = get_post_type( $post_id );
				$this->_write_log( "Post Type: $post_type", false, true );

				// Make a clear distinction to which pieces of the URL
				// are a vertical and which is a category
				$post_terms['vertical']['term_in_post_url'] = $permalink_path_pieces[0];
				$post_terms['category']['term_in_post_url'] = $permalink_path_pieces[1];

				// Identify which of the post's terms we'll remove
				foreach ( $post_terms as $taxonomy => $terms_data ) {
					if ( ! empty( $terms_data['terms'] ) && is_array( $terms_data['terms'] ) ) {

						// If the term is not used in the post URL...
						//if ( ! in_array( $terms_data['term_in_post_url'], $terms_data['terms'] ) ) {
						foreach ( $terms_data['terms'] as $term ) {
							if ( $terms_data['term_in_post_url'] !== $term->slug ) {

								// Store the term for removal..
								$terms_to_remove[ $taxonomy ][] = $term;

								// Keep an overall/running count of all verticals and categories being removed
								$dryrun_count_of_terms_removed[ $taxonomy ]++;

								if ( $this->dry_run ) {
									$this->_write_log( "Would remove $term->taxonomy $term->slug", false, true );
								} else {
									$this->_write_log( "Removing $term->taxonomy $term->slug ..", false, true );
								}
							}
						}
					}
				}

				// Remove terms from posts
				if ( ! $this->dry_run ) {
					if ( ! empty( $terms_to_remove ) && is_array( $terms_to_remove ) ) {
						$tt_ids_to_remove = array();

						// Create an array of the post's term's taxonomy ids
						// which we'll use to remove the post's terms
						foreach ( $terms_to_remove as $taxonomy => $terms ) {
							$tt_ids_to_remove = array_merge( $tt_ids_to_remove, wp_list_pluck( $terms, 'term_taxonomy_id' ) );
						}

						// wp_remove_object_terms() is slow..
						// It makes an additional SQL SELECT to verify if the terms exist
						// The following is pulled right out of wp_remove_object_terms()
						// minus the additional check that the post/term relationship exists.
						// We just pulled the post's terms—we know the relationship exists.
						// Let's cut to the chase and call the DELETE directly
						$in_tt_ids = "'" . implode( "', '", $tt_ids_to_remove ) . "'";

						$deleted = $wpdb->query( $wpdb->prepare(
							"DELETE FROM $wpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id IN ($in_tt_ids)",
							$post_id
						) );

						// After the deletion..
						// log some data about the terms which were removed
						if ( (bool) $deleted ) {

							// Log the terms for which we'll need to recount term relationships for
							foreach ( $terms_to_remove as $taxonomy => $terms ) {

								// Keep a unique array of terms which will need to
								// have their relationships recounted.
								$count_these_term_relationships[ $taxonomy ] = array_unique(
									array_merge(
										$count_these_term_relationships[ $taxonomy ],
										wp_list_pluck( $terms, 'term_taxonomy_id' )
									)
								);

								// Keep an total count of terms removed from posts
								$count_of_terms_removed[ $taxonomy ]++;

								// And clear the taxonomy's relationships cache
								wp_cache_delete( $post_id, $taxonomy . '_relationships' );
							}

							$this->_write_log( 'SUCCESS', false, true );
						} else {
							$this->_write_log( 'FAILED', false, true );
						}
					}
				}

				// Build the array of data we'll log to CSV about this post
				$post_data_to_log = array(
					$post_id,
					$post_type,
					$permalink,
				);

				foreach ( $post_terms as $taxonomy => $terms_data ) {
					if ( ! empty( $terms_data['terms'] ) && is_array( $terms_data['terms'] ) ) {
						$post_data_to_log[] = implode( ', ', wp_list_pluck( $terms_data['terms'], 'slug' ) );

						if ( ! empty( $terms_to_remove[ $taxonomy ] ) && is_array( $terms_to_remove[ $taxonomy ] ) ) {
							$post_data_to_log[] = count( $terms_to_remove[ $taxonomy ] );
							$post_data_to_log[] = implode( ', ', wp_list_pluck( $terms_to_remove[ $taxonomy ], 'slug' ) );
							$post_data_to_log[] = implode( ', ', wp_list_pluck( $terms_to_remove[ $taxonomy ], 'term_id' ) );
						} else {
							$post_data_to_log[] = 0;
							$post_data_to_log[] = '';
							$post_data_to_log[] = '';
						}
					} else {
						$post_data_to_log[] = '';
						$post_data_to_log[] = 0;
						$post_data_to_log[] = '';
						$post_data_to_log[] = '';
					}
				}

				// Write information about this post to the CSV log
				$csv_filename = $this->write_to_csv(
					$csv,
					array(), // no headers needed here since we're appending
					array( $post_data_to_log ),
					null,
					'a' // append onto the end of the csv file
				);

				// Increment the progress bar and our internal batch counter of posts processed
				$this->batch_update_progress_bar();

				$this->_write_log( "--------------------------", false, true );
			}
		);

		// Update term relationship counts
		// wp_update_term_count contains a lot of nested logic and a few sql calls
		// let's just run it once at the end of each batch
		if ( ! empty( $count_these_term_relationships ) && is_array( $count_these_term_relationships ) ) {
			foreach( $count_these_term_relationships as $taxonomy => $term_ids ) {
				if ( ! empty( $term_ids ) && is_array( $term_ids ) ) {
					$this->_write_log( "Generating term relationship counts for the following " . count( $term_ids ) . " $taxonomy terms ..", false );
					$this->_write_log( implode( ', ', $term_ids ), false );
					wp_update_term_count( $term_ids, $taxonomy );
				}
			}
			$this->_write_log( "--------------------------", false );
		}

		// Logging output to the screen
		if ( $count_of_posts_wo_vc_structure > 0 ) {
			$this->_write_log( "Skipped $count_of_posts_wo_vc_structure post(s) which are not using the vertical/category structure." );
		}

		if ( $count_of_posts_w_multiple_terms > 0 ) {
			$this->_write_log( "$count_of_posts_w_multiple_terms post(s) found with multiple vertical and/or category terms." );
		} else {
			$this->_write_log( 'No posts with multiple vertical and/or category terms found', true );
		}
		if ( $count_of_posts_wo_multiple_terms > 0 ) {
			$this->_write_log( "Skipped $count_of_posts_wo_multiple_terms post(s) found without multiple vertical and/or category terms." );
		}

		if ( $count_of_posts_w_multiple_terms > 0 ) {
			if ( $this->dry_run ) {
				foreach ( $dryrun_count_of_terms_removed as $taxonomy => $count ) {
					$this->_write_log( "$count $taxonomy term(s) would have been removed from $count_of_posts_w_multiple_terms post(s)" );
				}
			} else {
				foreach ( $count_of_terms_removed as $taxonomy => $count ) {
					$this->_write_log( "$count $taxonomy term(s) have been removed from $count_of_posts_w_multiple_terms post(s)" );
				}
			}

			$this->_write_log( "Detailed log written to $this->log_file" );
			$this->_write_log( "CSV log written to $csv" );

		}
	}

	/**
	 * Intake a CSV of categories, select posts with those categories,
	 * and create/assign post_tags with the same category names.
	 *
	 * ## OPTIONS
	 *
	 * --post-type=<post-type>
	 * : Only process posts in these post types. Comma-delimited string.
	 *
	 * --csv-mapping-file=<file>
	 * : The csv file contains the mapping from category to tag translation
	 *
	 * [--start-date=<yyyy-mm-dd>]
	 * : Start date
	 *
	 * [--end-date=<yyyy-mm-dd>]
	 * : End date
	 *
	 * [--batch-size=<batch-size>]
	 * : The WP_Query posts_per_page argument you wish to set (number of batches)
	 *
	 * [--sleep=<sleep>]
	 * : The number of seconds to sleep between each batch
	 *
	 * [--dry-run]
	 * : No operations are carried out while in this mode
	 *
	 * [--need-confirm]
	 * : Prompt for confirmation before begin
	 *
	 * [--batch-paged]
	 * : The WP_Query page to start on. Helpful for picking up where you left off.
	 *
	 * [--batch-max-paged]
	 * : The maximum number of wp_query pages to iterate through. Helpful for running a small test batch.
	 *
	 * [--log-file=<log-file>]
	 * : Path/Filename to the log file
	 *
	 * ## EXAMPLES
	 *
	 *     wp pmc-taxonomy create-tags-from-categories --post_type=post --categories=example
	 *
	 * @subcommand create-tags-from-categories
	 */
	public function create_tags_from_categories( $args, $assoc_args ) {

		// Extract common args sets up class internal vars for
		// common args like --dry-run, --batch-size, --sleep, etc.
		$this->_extract_common_args( $assoc_args );

		$this->_write_log( sprintf('Create tags from categories script start%s', $this->dry_run ? ' - Dry run' : '' ) );

		if ( ! empty( $this->log_file ) ) {
			WP_CLI::line( "Writing log file to $this->log_file ..", true );
		}

		if ( !file_exists( $assoc_args['csv-mapping-file'] ) ) {
			$this->_error( sprintf( 'Error, find not found %s', $assoc_args['csv-mapping-file'] )  );
		}

		$fp = fopen( $assoc_args['csv-mapping-file'], 'r' );
		if ( ! $fp ) {
			$this->_error( sprintf( 'Error reading file %s', $assoc_args['csv-mapping-file'] )  );
		}

		$headers = fgetcsv( $fp );

		if ( false === array_search( 'category', $headers ) || false === array_search( 'tag', $headers ) ) {
			fclose( $fp );
			$this->_error( sprintf( 'Invalid csv file, missing column category / tag' ) );
		}

		// We need to load the category to tag mapping from csv
		$cat_to_tag_name_translate = [];
		while ( $row = fgetcsv( $fp ) ) {
			$row = array_combine( $headers, $row );
			// if tag not specified, default to category name
			$cat_to_tag_name_translate[ $row['category'] ] = ! empty( $row['tag'] ) ? $row['tag'] : $row['category'];
		}

		fclose( $fp );

		// extract the category names from array keys
		$categories = array_keys( $cat_to_tag_name_translate );
		// extract the tag names from array values
		$tags = array_values( $cat_to_tag_name_translate );

		// Define the post types, csv dir, and full path to the csv file
		$post_types = explode( ',', sanitize_text_field( $assoc_args['post-type'] ) );
		$this->_write_log( 'Apply to post type(s): ' . implode( ', ', $post_types ) );
		$this->_write_log( 'Categories: ' . implode( ', ', $categories ) );
		$this->_write_log( "--------------------------" );

		$this->_confirm_before_continue( 'Proceed?' );

		// validate categories
		$cat_terms = [];
		foreach ( $categories as $category_name ) {
			$term = term_exists( $category_name, 'category' );
			if ( ! $term ) {
				$this->_error( sprintf( "Invalid category: %s", $category_name ) );
			}
			$term = get_term( $term['term_id'], 'category' );
			if ( is_wp_error( $term ) ) {
				$this->_error( sprintf( "Invalid category: %s", $category_name ) );
			}
			$cat_terms[] = $term;
		}

		// create the real category term to tag taxonomy term object mapping.
		$cat_to_tag_maps = [];
		foreach ( $cat_terms as $cat ) {
			$tag_name = $cat->name;

			if ( !empty( $cat_to_tag_name_translate[ $tag_name ] ) ) {
				$tag_name = $cat_to_tag_name_translate[ $tag_name ];
			}
			$term = term_exists( $tag_name, 'post_tag' );

			if ( empty( $term ) ) {
				if (  $this->dry_run ) {
					$this->_write_log( sprintf( 'Would create tag: %s', $tag_name ) );
					continue;
				}
				$term = wp_insert_term( $tag_name, 'post_tag' );
				if ( is_wp_error( $term ) ) {
					$this->_error( sprintf('Error trying to add post tag: %s', $tag_name ) );
				}
			}

			$cat_to_tag_maps[ $cat->slug ] = get_term( $term['term_id'], 'post_tag' );

		}

		$task_runner_callback = function( $post_id ) use( $cat_to_tag_maps, $cat_to_tag_name_translate ) {

			// ultilizing wp cached function
			$terms = get_the_terms( $post_id, 'category' );
			if ( ! $terms || is_wp_error( $terms ) ) {
				return;
			}

			$tag_ids = [];
			$tag_names = [];
			foreach( $terms as $term ) {
				if ( ! isset( $cat_to_tag_maps[ $term->slug ] ) ) {
					// if it's dry run, the mapping should be in $cat_to_tag_name_translate if tag have not been created
					if ( $this->dry_run && isset( $cat_to_tag_name_translate[ $term->name ] ) ) {
						$tag_names[] = $cat_to_tag_name_translate[ $term->name ];
					}
					continue;
				}
				$tag_ids[] = $cat_to_tag_maps[ $term->slug ]->term_id;
				$tag_names[] = $cat_to_tag_maps[ $term->slug ]->name;
			}
			if ( !empty( $tag_names ) ) {
				$this->_write_log( sprintf('%d - %s', $post_id, get_the_title( $post_id ) ) );
				if ( ! $this->dry_run ) {
					wp_add_object_terms( $post_id, $tag_ids, 'post_tag' );
					$this->_write_log( sprintf(' -> assigned tags: %s', implode( ', ', $tag_names ) ) );
				} else {
					$this->_write_log( sprintf(' -> would assign tags: %s', implode( ', ', $tag_names ) ) );
				}
			}

		}; // $task_runner_callback

		$args = array(
				'post_type'   => $post_types,
				'post_status' => 'any',
				'fields'      => 'ids',
				'tax_query'   => array(
					array(
						'taxonomy' => 'category',
						'terms'    => wp_list_pluck( $cat_terms, 'term_id' ),
						'field'    => 'term_id',
						'operator' => 'IN',
					),
				),
			);

		// filter by start & end date
		if ( isset( $assoc_args['start-date'] ) ) {
			$start_dtime = strtotime( $assoc_args['start-date'] );
			if ( isset( $assoc_args['end-date'] ) ) {
				$end_dtime = strtotime( $assoc_args['end-date'] );
			} else {
				$end_dtime = strtotime("+1 day");
			}
			$args['date_query'] = [
					'after'     => date('Y-m-d H:i:s', $start_dtime ),
					'before'    => date('Y-m-d H:i:s', $end_dtime ),
					'inclusive' => true,
				];
			$this->_write_log( sprintf("Filter by date range: %s to %s", date('Y-m-d H:i:s', $start_dtime), date('Y-m-d H:i:s', $end_dtime ) ) );
		}

		// call task runner to process data in batch
		$this->batch_wp_query_task_runner( $args, $task_runner_callback );

		$this->_write_log( sprintf('Task finished%s', $this->dry_run ? ' - Dry run' : '' ) );

	}

	/**
	 * Method to get the term_id of a term queried via slug
	 *
	 * @param string $term_slug Slug of the term whose ID is to be fetched
	 * @param string $taxonomy Taxonomy of the term being queried
	 * @return int Returns term_id of the term else 0 on failure
	 */
	protected function _get_term_id( $term_slug, $taxonomy = 'category' ) {

		if ( empty( $term_slug ) || empty( $taxonomy ) || ! is_string( $term_slug ) || ! is_string( $taxonomy ) ) {
			//parameters aren't what we expect, bail out
			return 0;
		}

		$term = get_term_by( 'slug', $term_slug, $taxonomy, OBJECT );

		if ( ! is_a( $term, 'WP_Term' ) || empty( $term->term_id ) || ! is_numeric( $term->term_id ) ) {
			return 0;
		}

		return intval( $term->term_id );

	}

	/**
	 * Method to remove a particular term from a post
	 *
	 * @param int $post_id ID of the post
	 * @param int $term_id ID of the term to remove
	 * @param string $taxonomy Taxonomy of the term
	 * @return bool Returns TRUE on success, FALSE on failure
	 */
	protected function _remove_term_from_post( $post_id, $term_id, $taxonomy = 'category' ) {

		if (
			empty( $post_id ) || empty( $term_id ) || empty( $taxonomy )
			|| ! is_int( $post_id ) || $post_id < 1 || ! is_int( $term_id ) || ! is_string( $taxonomy )
		) {
			//parameters aren't what we expect, bail out
			return false;
		}

		//get the existing terms on the post in the same taxonomy
		$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );

		if ( empty( $terms ) ) {
			//post doesn't have any terms in the same taxonomy
			//nothing to be done, bail out
			return true;
		}

		$term_id = intval( $term_id );
		$terms = array_unique( array_map( 'intval', $terms ) );

		$terms_to_keep = array();

		//loop over all terms and gather the ones we want to keep
		for ( $i = 0; $i < count( $terms ); $i++ ) {

			if ( $terms[ $i ] > 0 && $terms[ $i ] !== $term_id ) {
				$terms_to_keep[] = $terms[ $i ];
			}

		}

		$terms_to_keep = array_unique( array_map( 'intval', $terms_to_keep ) );

		//set terms on the post, overwriting existing terms in the same taxonomy
		$result = wp_set_object_terms( $post_id, $terms_to_keep, $taxonomy, false );

		if ( is_array( $result ) && ! is_wp_error( $result ) ) {
			//all done
			return true;
		}

		return false;

	}

	/**
	 * Method to replace a particular term from a post with another term
	 *
	 * @param int $post_id ID of the post
	 * @param int $from_category_id ID of the term to remove
	 * @param int $to_category_id ID of the term to add
	 * @param string $taxonomy Taxonomy of the term
	 * @return bool Returns TRUE on success, FALSE on failure
	 */
	protected function _replace_term_on_post( $post_id, $from_category_id, $to_category_id, $taxonomy = 'category' ) {

		if (
			empty( $post_id ) || empty( $from_category_id ) || empty( $to_category_id ) || empty( $taxonomy )
			|| ! is_int( $post_id ) || $post_id < 1
			|| ! is_int( $from_category_id ) || $from_category_id < 1
			|| ! is_int( $to_category_id ) || $to_category_id < 1
			|| ! is_string( $taxonomy )
		) {
			//parameters aren't what we expect, bail out
			return false;
		}

		//remove term
		$result_term_removal = $this->_remove_term_from_post( $post_id, $from_category_id, $taxonomy );

		if ( $result_term_removal !== true ) {
			//unable to remove term, something is wrong
			//bail out
			return false;
		}

		//add term
		$result_term_addition = wp_set_object_terms( $post_id, $to_category_id, $taxonomy, true );

		if ( is_array( $result_term_addition ) && ! is_wp_error( $result_term_addition ) ) {
			//all done
			return true;
		}

		return false;

	}

	/**
	 * Replace a category on posts with another category
	 *
	 * @subcommand replace-category-on-posts
	 *
	 * ## OPTIONS
	 *
	 * --from=<from-category-slug>
	 * : Slug of category which is to be removed
	 *
	 * --to=<to-category-slug>
	 * : Slug of category which is to be added
	 *
	 * [--post-type=<post-type>]
	 * : Type of post on which this command is to be run. Defaults to 'post' type.
	 *
	 * --log-file=<log-file>
	 * : Path/Filename to the log file
	 *
	 * [--dry-run]
	 * : No operations are carried out while in this mode
	 *
	 * ## EXAMPLES
	 *
	 *		wp pmc-taxonomy replace-category-on-posts --from=category-1 --to=category-2 --post-type=post --log-file=/var/log/pmc-taxonomy-replace-category-on-posts.log
	 *
	 * @ticket PMCVIP-2181
	 */
	public function replace_category_on_posts( $args = array(), $assoc_args = array() ) {

		/*
		 * Defaults
		 */
		$assoc_args['sleep'] = 1;
		$assoc_args['batch-size'] = 25;
		$assoc_args['taxonomy'] = 'category';

		$this->_assoc_args_properties_mapping = array(
			'from_category' => 'from',
			'to_category'   => 'to',
			'post_type'     => 'post-type',
			'log_file'      => 'log-file',
		);

		$this->_extract_common_args( $assoc_args );

		if ( empty( $this->log_file ) ) {
			$this->_error( 'File path for Log need to be specified before this command can be run' );
			return;
		}

		if ( empty( $this->from_category ) || empty( $this->to_category ) ) {
			$this->_error( 'Slugs for both category to be removed and category to be added must be specified' );
			return;
		}

		if ( empty( $this->post_type ) ) {
			$this->post_type = 'post';
		}

		$this->post_type = sanitize_title( $this->post_type );
		$this->from_category = sanitize_title( $this->from_category );
		$this->to_category = sanitize_title( $this->to_category );

		$offset = 0;
		$taxonomy = 'category';
		$from_category_id = $this->_get_term_id( $this->from_category, $taxonomy );
		$to_category_id = $this->_get_term_id( $this->to_category, $taxonomy );

		if ( $from_category_id < 1 ) {
			$this->_error( sprintf( 'Unable to fetch term object for slug: %s', $this->from_category ) );
			return;
		} elseif ( $to_category_id < 1 ) {
			$this->_error( sprintf( 'Unable to fetch term object for slug: %s', $this->to_category ) );
			return;
		}

		$post_fetch_args = array(
			'posts_per_page'   => $this->batch_size,
			'offset'           => $offset,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_type'        => $this->post_type,
			'post_status'      => 'publish',
			'suppress_filters' => false,
			'tax_query'        => array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $from_category_id,
				),
			),
		);

		$posts = get_posts( $post_fetch_args );

		if ( empty( $posts ) || is_wp_error( $posts ) ) {
			$this->_error( sprintf( 'No posts found in category "%s"', $this->from_category ) );
			return;
		}

		$iteration = 0;
		$success_count = 0;
		$total_posts_fetched = count( $posts );
		$batch = 1;
		$posts_start = 1;
		$posts_end = $total_posts_fetched;

		while( ! empty( $posts ) ) {

			$this->_write_log( sprintf( 'Starting Batch: %d - Posts: %d to %d', $batch, $posts_start, $posts_end ) );

			foreach ( $posts as $post ) {

				if ( ! $this->dry_run ) {

					//replace category
					$result_cat_change = $this->_replace_term_on_post( $post->ID, $from_category_id, $to_category_id, $taxonomy );

					if ( $result_cat_change === true ) {

						$success_count++;
						$this->_write_log( sprintf( 'Post ID: %d - category replaced from "%s" to "%s"', $post->ID, $this->from_category, $this->to_category ) );

					} else {
						$this->_write_log( sprintf( 'Post ID: %d - category NOT replaced from "%s" to "%s"', $post->ID, $this->from_category, $this->to_category ) );
					}

					unset( $result_cat_change );

				} else {
					$success_count++;
					$this->_write_log( sprintf( 'Post ID: %d - processed in dry run', $post->ID ) );
				}

			}	//end posts loop

			$this->_write_log( sprintf( 'Ending Batch: %d - Posts: %d to %d', $batch, $posts_start, $posts_end ) );
			$this->_write_log( "\n" );

			/*
			 * Sleep for a bit after running through a batch
			 * and call $this->stop_the_insanity()
			 * to scrub the object cache
			 */
			if ( $this->sleep > 0 ) {

				$this->_write_log( sprintf( 'Sleeping for %d second...', $this->sleep ) );
				$this->_write_log( "\n" );

				$this->stop_the_insanity();
				sleep( $this->sleep );

			}

			$post_fetch_args['offset'] += $total_posts_fetched;

			$posts = get_posts( $post_fetch_args );

			if ( empty( $posts ) || is_wp_error( $posts ) ) {
				$this->_write_log( sprintf( 'All posts migrated from category "%s"', $this->from_category ) );
				break;
			}

			$batch++;
			$posts_start += $total_posts_fetched;

			$total_posts_fetched = count( $posts );
			$posts_end += $total_posts_fetched;

		}	//end of batch loop

		$this->_write_log( sprintf( '%d of %d posts successfully processed', $success_count, $posts_end ) );

	}	//replace_category_on_posts()

}	//end class

WP_CLI::add_command( 'pmc-taxonomy', 'PMC_WP_CLI_Taxonomy' );


// EOF