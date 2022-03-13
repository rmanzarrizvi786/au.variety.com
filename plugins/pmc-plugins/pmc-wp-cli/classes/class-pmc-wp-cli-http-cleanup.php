<?php

/**
 * PMC_WP_CLI_Http_Cleanup Script : Get HTTP Assets in post content / Update to https
 * @since 2018-05-11
 * @version 2018-05-11 Markham F Rollins IV
 */

WP_CLI::add_command( 'pmc-http-cleanup', 'PMC_WP_CLI_Http_Cleanup' );

class PMC_WP_CLI_Http_Cleanup extends PMC_WP_CLI_Base {

	/**
	 * Create CSV file for specific site that lists out all HTTP references, the post ID, post date and content type
	 *
	 * ## OPTIONS
	 *
	 * --csv-file=<csv-file>
	 * : The absolute directory path where to write csv output files.
	 *
	 * --need-confirm<need-confirm>
	 * : To require confirmation to overwrite existing file or not
	 *
	 * [--post-type=<post-type>]
	 * : The post type to query
	 *
	 * [--log-file=<log-file>]
	 * : Log file to use for this cli script
	 *
	 * [--email=<email>]
	 * : Email to send notification after script complete.
	 *
	 * [--email-when-done=< / yes>]
	 * : Whether to send notification or not.
	 *
	 * [--dry_run]
	 * : Don't actually do anything
	 *
	 * ## EXAMPLES
	 *
	 *     wp pmc-http-cleanup get_references --url=footwearnews.vip.local --csv-file=/srv/fn-data/http.csv --post-type=post --email=dist.dev@pmc.com --email-when-done=yes --need-confirm=false
	 *
	 * @synopsis --csv-file=<csv-file> --need-confirm=<need-confirmed> [--post-type=<post-type>] [--logfile=<log-file>] [--email=<email>] [--email-when-done] [--dry-run]
	 */
	public function get_references( $args = [], $assoc_args = [] ) {

		$this->_extract_common_args( $assoc_args );

		$this->_notify_start( 'Starting: Get http references' );

		if ( ! empty( $assoc_args['csv-file'] ) ) {
			$csv_file = $assoc_args['csv-file'];
		}

		if ( file_exists( $csv_file ) ) {
			$this->_confirm_before_continue( "CSV export file '$csv_file' already exists, overwrite it?" );
		}

		$csv_output = [];
		$csv_files  = [];

		// Batch load the posts / post-type provided
		$this->batch_wp_query_task_runner(
			[
				'post_type'   => $assoc_args['post-type'] ? $assoc_args['post-type'] : '',
				'post_status' => 'publish',
			],
			function( $post ) use ( $csv_file, &$csv_output, &$csv_files ) {
				/**
				 * This regex to written to grab all http references in the code it's matching against.
				 *
				 * It will match the entire URL including query parameters if they are included. There are no matching groups as they are all ignored.
				 *
				 * For explanation of groups/matching string please see https://regexr.com/3ppa1
				 *
				 * Examples:
				 *  http://example.com
				 *  http://example.com?testing=this
				 *  http://example.com/image.jpg
				 *  http://example.com/image.jpg?testing=this
				 *  http://www.example.com
				 *  https://example.com (No match)
				 */
				$https_assets_regex = '/http\:\/\/(?:www\.)?(?:[a-zA-Z0-9]+\.[a-zA-Z]{2,3})*(?:[^\s\?\"\<\>]+)?(?:\?[a-zA-Z0-9\-\.]+(?:\=[a-zA-Z0-9\-\.]+)(?:&(?:[a-zA-Z0-9\-\.]+)(?:\=[a-zA-Z0-9\-\.]+)?)*)*/';
				preg_match_all( $https_assets_regex, $post->post_content, $matches );

				if ( $matches[0] ) {
					foreach ( $matches[0] as $match ) {
						$https_url = str_replace( 'http://', 'https://', $match );

						// Only get headers for off site content
						if ( false === strpos( $match, get_site_url() ) ) {
							$headers       = get_headers( $match, 1 );
							$https_headers = get_headers( $https_url, 1 );

							$csv_output[] = [
								$post->ID,
								$post->post_date,
								$headers[0],
								is_array( $headers['Content-Type'] ) ? implode( $headers['Content-Type'], ',' ) : $headers['Content-Type'],
								$match,
								$https_headers[0],
								$https_url,
								'',
							];
						} else {
							$csv_output[] = [
								$post->ID,
								$post->post_date,
								'',
								'',
								$match,
								'',
								$https_url,
								'',
							];
						}
					}
				}

				// Increment the progress bar and our internal batch counter of posts processed
				$this->batch_update_progress_bar();

				// CSV of posts with HTTP references
				$this->batch_increment_csv(
					[
						'id',
						'post_date',
						'status',
						'content_type',
						'url',
						'https_content_type',
						'https_url',
						'update',
					],
					$csv_output,
					$csv_file,
					$this->batch_num_posts_processed,
					$this->batch_size,
					$this->batch_found_posts
				);

				if ( $this->is_end_of_batch() ) {
					// Keep track of the rotated riles
					$csv_filename = $this->possibly_rotate_file( $csv_file, 10000000 ); // Rotate files at 10MB filesize

					if ( $csv_file !== $csv_filename ) {
						$csv_files[] = $csv_filename;
					}

					// Cleanup
					unset( $post, $https_assets_regex, $matches );
					$csv_output = [];
				}
			}
		);

		// Ensure that the base file is attached to email if we never rotated it out
		if ( empty( $csv_files ) ) {
			$csv_files[] = $csv_file;
		}

		$this->_notify_done( sprintf( 'HTTP Cleanup WP-CLI command on %s completed, generated the following CSV files: %s', WP_CLI::get_config( 'url' ), implode( $csv_files, ', ' ) ), $csv_files );
	}

	/**
	 * Read CSV file for specific site that will updates URLS based on an update column
	 *
	 * ## OPTIONS
	 *
	 * --csv-file=<csv-file>
	 * : The absolute directory path where csv files exists.
	 *
	 * [--log-file=<log-file>]
	 * : Log file to use for this cli script
	 *
	 * [--dry_run]
	 * : Don't actually do anything
	 *
	 * ## EXAMPLES
	 *
	 *     wp pmc-http-cleanup cleanup_references --url=footwearnews.vip.local --csv-file=/srv/fn-data/http.csv
	 *
	 * @synopsis --csv-file=<csv-file> [--log-file=<log-file>] [--dry-run]
	 */
	public function cleanup_references( $args = [], $assoc_args = [] ) {

		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		$this->_extract_common_args( $assoc_args );

		$this->_notify_start( 'Starting: Update http references' );

		if ( ! empty( $assoc_args['csv-file'] ) ) {
			$csv_file = $assoc_args['csv-file'];
		}

		if ( ! file_exists( $csv_file ) ) {
			$this->_error( sprintf( 'CSV file %s does not exist', $csv_file ) );
		}

		// Open the CSV to parse
		$http_url_file = fopen( $csv_file, 'r' ); //@codingStandardsIgnoreLine: WP_Filesystem not appropriate for this logic.

		if ( ! $http_url_file ) {
			$this->_error( sprintf( 'CSV file %s could not be opened', $csv_file ) );
		}

		// Get header row
		$column_headers = fgetcsv( $http_url_file );

		if ( ! $column_headers ) {
			$this->_error( sprintf( 'CSV file %s is empty', $csv_file ) );
		}

		// Required columns
		$required_column_headers = [
			'id',
			'update',
			'url',
			'https_url',
		];
		$missing_columns         = [];

		// Determine if the file is valid
		foreach ( $required_column_headers as $required_column_header ) {
			if ( ! in_array( $required_column_header, $column_headers ) ) { //@codingStandardsIgnoreLine: $column_headers is always an array returned from fgetcsv
				$missing_columns[] = $required_column_header;
			}
		}

		if ( $missing_columns ) {
			$this->_error( sprintf( 'CSV file %s is missing the following required column/s: %s', $csv_file, implode( $missing_columns, ', ' ) ) );
		}

		// Counter to output how many posts are updated
		$update_counter            = 0;
		$successful_update_counter = 0;

		while ( false !== ( $row = fgetcsv( $http_url_file ) ) ) { //@codingStandardsIgnoreLine: Looping over CSV and need to do inline assignment with check for if it exists
			$item_to_update = array_combine( $column_headers, $row );

			if ( 'y' === $item_to_update['update'] ) {
				$update_counter++;     // Update the counter

				// Update the post
				$post = get_post( $item_to_update['id'] );

				if ( $post ) {
					if ( ! $this->dry_run ) {
						$post_id = wp_update_post([
							'ID'           => $item_to_update['id'],
							'post_content' => str_replace( $item_to_update['url'], $item_to_update['https_url'], $post->post_content ),
						]);

						// Write an error if it occured
						if ( is_wp_error( $post_id ) ) {
							$this->_error( sprintf( 'Post id %d failed to update', $item_to_update['id'] ) );
						} else {
							$successful_update_counter++;     // Update the successful counter
						}
					}

					$this->_write_log( sprintf( 'Post ID %d: Updating URL %s to %s', $item_to_update['id'], $item_to_update['url'], $item_to_update['https_url'] ) );
				} else {
					$this->_warning( sprintf( 'Post ID  %d not found, cannot update', $item_to_update['id'] ) );
				}
			}

			$this->_update_iteration();
		}

		fclose( $http_url_file ); //@codingStandardsIgnoreLine: WP_Filesystem not appropriate for this logic.

		$this->_success( sprintf( 'Updated %d of %d posts to have HTTPS content', $successful_update_counter, $update_counter ) );
	}
}
