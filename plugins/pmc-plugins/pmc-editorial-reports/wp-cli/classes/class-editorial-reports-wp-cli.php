<?php
/**
 * Editorial Reports CLI Commands.
 *
 * @author  Kelin Chauhan <kelin.chauhan@rtcamp.com>
 *
 * @package pmc-editorial-reports
 */

namespace PMC\Editorial_Reports\WP_CLI;

/**
 * CLI Commands for Editorial Reports.
 *
 */
class Editorial_Reports extends \PMC_WP_CLI_Base {
	/**
	 * WP-CLI command to generate report meta data and store in meta table. If --meta-fields option is empty then generates all metadata for all the posts.
	 *
	 * ## OPTIONS
	 *
	 * [--meta-fields]
	 * : Meta fields to generate data for. Accepted values: word_count, image_count, categorization. Accepts comma seperated values. If empty then generates all the metadata.
	 *
	 * [--post-types]
	 * : Post Types to generate metadata for. Accepted Values: post, pmc-gallery, pmc-video, pmc-lists. Accepts comma seperated values. If empty then generates metadata for all the supported post types.
	 *
	 * [--csv-report-file]
	 * : If provided then will write report to the CSV file.
	 * ---
	 * default: false
	 * options:
	 *   - true
	 *   - false
	 * ---
	 *
	 * [--dry-run]
	 * : Whether or not to do dry run
	 * ---
	 * default: false
	 * options:
	 *   - true
	 *   - false
	 * ---
	 *
	 * [--log-file=<file>]
	 * : Path to the log file.
	 *
	 * [--email]
	 * : Email to send notification after script complete.
	 *
	 * [--email-when-done]
	 * : Whether to send notification or not.
	 *
	 * [--email-logfile]
	 * : Whether to send log file or not.
	 *
	 * [--sleep=<sleep>]
	 * : Set sleep in seconds to throttle the script after calling stop the insanity, default = 2 (seconds)
	 *
	 * [--max-iteration=<iteration>]
	 * : Set max iteration for stop the insanity, default = 20
	 *
	 * [--batch-size=<number>]
	 * : Batch size
	 *
	 * ## EXAMPLES
	 *
	 *    # Generate Metadata for all the brands and all the post types.
	 *    $ wp pmc-editorial-reports generate-metadata
	 *
	 * @subcommand generate_metadata
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function generate_metadata( $args = array(), $assoc_args = array() ) {

		$this->_extract_common_args( $assoc_args );

		$this->_notify_start( 'Start: Generating metadata' );

		$supported_post_types = [
			'post',
			'pmc-gallery',
			'pmc_list',
			'pmc-video',
		];

		$supported_meta_fields = [
			'word_count',
			'image_count',
			'categorization',
		];

		$meta_fields     = ( ! empty( $assoc_args['meta-fields'] ) ) ? explode( ',', $assoc_args['meta-fields'] ) : $supported_meta_fields;
		$post_types      = ( ! empty( $assoc_args['post-types'] ) ) ? explode( ',', $assoc_args['post-types'] ) : $supported_post_types;
		$csv_report_file = ( ! empty( $assoc_args['csv-report-file'] ) ) ? $assoc_args['csv-report-file'] : false;

		// Error out if provided meta fields don't belong to supported meta fields.
		if ( empty( array_intersect( $supported_meta_fields, $meta_fields ) ) ) {
			$this->_error( sprintf( 'Please provide meta fields from list of supported values: %s', implode( ', ', $supported_meta_fields ) ) );
		}

		//  Error out if provided post-types don't belong to supprted post-types.
		if ( empty( array_intersect( $supported_post_types, $post_types ) ) ) {
			$this->_error( sprintf( 'Please provide post-type from list of supported post-types: %s', implode( ', ', $supported_post_types ) ) );
		}

		$processed = 0;

		if ( ! empty( $csv_report_file ) ) {
			$csv_report_file = $this->write_to_csv(
				$csv_report_file,
				[],
				[
					[
						'Post Type',
						'Article ID',
						'Article URL',
						'Article Taxonomy ( Category + Sub Category )',
						'Article Taxonomy ( Vertical )',
						'Word Count',
						'Number of Image',
						'Attached Galleries',
					],
				]
			);

			$this->_write_log( sprintf( 'Writing CSV Report at: %s', $csv_report_file ), 0 );
			$this->_write_log( '', 0 );
		}

		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true ); // @codeCoverageIgnore
		}

		$this->start_bulk_operation();

		// Iterate over post types.
		foreach ( $post_types as $post_type ) {

			$post_type = trim( $post_type );

			// Skip if post type is not supported.
			if ( ! in_array( $post_type, (array) $supported_post_types, true ) ) {

				$this->_write_log( sprintf( 'Skipped %s, post-type not supported!', $post_type ), 2 );
				continue;
			}

			$offset = 0;
			$args   = array(
				'post_type'        => $post_type,
				'post_status'      => 'publish',
				'suppress_filters' => false,
				'posts_per_page'   => $this->batch_size,
				'fields'           => 'ids',
			);

			do {

				$args['offset'] = $offset;
				$offset        += $this->batch_size;

				$posts_query = new \WP_Query( $args ); // phpcs:ignore
				$posts       = $posts_query->posts;

				if ( empty( $posts ) || ! is_array( $posts ) ) {
					break;
				}

				$report_data = [];

				foreach ( $posts as $post_id ) {

					$post_permalink = get_permalink( $post_id );

					$this->_write_log( sprintf( '%d Processing Post ID: %d ( URL: %s )', $processed, $post_id, $post_permalink ), 0 );

					// Get post word count.
					if ( in_array( 'word_count', (array) $meta_fields, true ) ) {

						// Get the word count.
						$word_count = \PMC_Editorial_Reports::get_instance()->get_post_word_count( $post_id );

						if ( ! $this->dry_run ) {

							// Store the number of images in post meta.
							$existing_word_count = get_post_meta( $post_id, '_pmc_word_count', true );

							if ( (int) $word_count !== (int) $existing_word_count ) {
								update_post_meta( $post_id, '_pmc_word_count', (int) $word_count );
							}
						}

						$this->_write_log( sprintf( 'Number of words: %d', $word_count ), 0 );
					}

					// Get number of images in the post.
					if ( in_array( 'image_count', (array) $meta_fields, true ) ) {

						$image_count = \PMC_Editorial_Reports::get_instance()->get_image_count( $post_id );

						if ( ! $this->dry_run ) {
							// Store the number of images in post meta.

							$existing_image_count = get_post_meta( $post_id, '_pmc_image_count', true );

							if ( (int) $image_count !== (int) $existing_image_count ) {
								update_post_meta( $post_id, '_pmc_image_count', (int) $image_count );
							}
						}

						$this->_write_log( sprintf( 'Number of images %d', (int) $image_count ), 0 );
					}

					// Get post categorization.
					if ( in_array( 'categorization', (array) $meta_fields, true ) ) {

						$categories = implode( ', ', \PMC_Editorial_Reports::get_instance()->get_post_taxonomy_categorization( $post_id, 'category' ) );
						$verticals  = implode( ', ', \PMC_Editorial_Reports::get_instance()->get_post_taxonomy_categorization( $post_id, 'vertical' ) );

						$this->_write_log( sprintf( 'Taxonomy Categorization: ', $categories, $verticals ), 0 );
						$this->_write_log( '---', 0 );
						$this->_write_log( sprintf( 'Category: %s', $categories ), 0 );
						$this->_write_log( sprintf( 'Vertical: %s', $verticals ), 0 );
						$this->_write_log( '---', 0 );

						if ( ! $this->dry_run ) {

							$existing_categorization = get_post_meta( $post_id, '_pmc_post_categorization', true );
							$updated_categorization  = wp_json_encode(
								[
									'category' => $categories,
									'vertical' => $verticals,
								]
							);

							if ( $existing_categorization !== $updated_categorization ) {

								// Store the categorization info in the post meta.
								update_post_meta(
									$post_id,
									'_pmc_post_categorization',
									$updated_categorization
								);
							}

						}

					}

					// If reporting is enabled then prepare the data.
					if ( ! empty( $csv_report_file ) ) {

						// Get post linked gallery.
						$linked_gallery = get_post_meta( $post_id, 'pmc-gallery-linked-gallery', true );

						if ( ! empty( $linked_gallery ) ) {
							$linked_gallery = json_decode( $linked_gallery );
							$linked_gallery = $linked_gallery->url;
						} else {
							$linked_gallery = '';
						}

						$report_row[] = get_post_type( $post_id );
						$report_row[] = $post_id;
						$report_row[] = $post_permalink;
						$report_row[] = $categories;
						$report_row[] = $verticals;
						$report_row[] = $word_count;
						$report_row[] = $image_count;
						$report_row[] = $linked_gallery;

						$report_data[] = $report_row;

						// Reset Data.
						$report_row = [];
					}

					$processed++;

					// Call sleep() and stop_the_insanity() methods after every --max-iteration iteration.
					$this->_update_iteration();

				}

				// Write data to file if reporting is required.
				if ( ! empty( $csv_report_file ) ) {

					$this->write_to_csv(
						$csv_report_file,
						[],
						$report_data,
						null,
						'a'
					);

					// Reset data.
					$report_data = [];
				}

			} while ( is_array( $posts ) && count( $posts ) === $this->batch_size && $this->batch_size > 0 );

		}

		$this->end_bulk_operation();

		$attachments = [];
		if ( ! empty( $csv_report_file ) ) {
			$attachments[] = $csv_report_file;
		}

		$this->_notify_done( sprintf( 'Processing Finished, Total Posts Processed: %d', $processed ), $attachments );

	}

	/**
	 * WP-CLI command to generate CSV report containing: Post Type, ID, Piblished Date, URL, Category, Vertical, Word Count, Number of Images, Attached Galleries.
	 *
	 * ## OPTIONS
	 *
	 * [--report-file]
	 * : Required, CSV file to write the report to.
	 *
	 * [--post-types]
	 * : Optional, Post Types to generate report for. Accepted Values: post, pmc-gallery, pmc-video, pmc_lists. Accepts comma seperated values. If empty then generates metadata for all the supported post types.
	 *
	 * [--from-date]
	 * : Optional, Posts before this date will be ignored. if --from-date is passed and --to-date is empty then report is generated for all the posts from the date in this option.
	 *
	 * [--to-date]
	 * : Optional, Posts after this date will be ignored. if --to-date is passed and --from-date is empty then report is generated for all the posts till the date in this option.
	 *
	 * [--log-file=<file>]
	 * : Path to the log file.
	 *
	 * [--email]
	 * : Email to send notification after script complete.
	 *
	 * [--email-when-done]
	 * : Whether to send notification or not.
	 *
	 * [--email-logfile]
	 * : Whether to send log file or not.
	 *
	 * [--sleep=<sleep>]
	 * : Set sleep in seconds to throttle the script after calling stop the insanity, default = 2 (seconds)
	 *
	 * [--max-iteration=<iteration>]
	 * : Set max iteration for stop the insanity, default = 20
	 *
	 * [--batch-size=<number>]
	 * : Batch size
	 *
	 * ## EXAMPLES
	 *
	 *    # Generate Metadata for all the brands and all the post types.
	 *    $ wp pmc-editorial-reports generate_report --report-file=./editorial-report.csv
	 *
	 *    # Generate report for pmc-gallery post type
	 *    $ wp pmc-editorial-reports generate_report --post-types=pmc-gallery --report-file=./editorial-report.csv
	 *
	 *    # Generate report for all post types for a specific date range
	 *    $ wp pmc-editorial-reports generate_report --start-date=27/10/2015 --end-date=03/03/2020
	 *
	 * @subcommand generate_report
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function generate_report( $args = array(), $assoc_args = array() ) {

		$this->_extract_common_args( $assoc_args );

		$this->_notify_start( 'Start: Generating Report' );

		// Error out if report file location is missing.
		if ( empty( $assoc_args['report-file'] ) ) {
			$this->_error( sprintf( "--report-file can't be empty!" ) );
			return;
		}

		$supported_post_types = [
			'post',
			'pmc-gallery',
			'pmc_list',
			'pmc-video',
		];

		$post_types      = ( ! empty( $assoc_args['post-types'] ) ) ? explode( ',', $assoc_args['post-types'] ) : $supported_post_types;
		$csv_report_file = $assoc_args['report-file'];
		$posts_after     = [];
		$posts_before    = [];
		$processed       = 0;

		//  Error out if provided post-types don't belong to supprted post-types.
		if ( empty( array_intersect( $supported_post_types, $post_types ) ) ) {
			$this->_error( sprintf( 'Please provide post-type from list of supported post-types: %s', implode( ', ', $supported_post_types ) ) );
			return;
		}

		// If --from-date option is provided.
		if ( ! empty( $assoc_args['from-date'] ) ) {

			$from_date = $this->validate_and_get_date( $assoc_args['from-date'] );

			if ( ! $from_date ) {
				$this->_error( 'Invalid value for --from-date! Ensure that the date is in dd-mm-yyyy format.' );
				return;
			}

			$posts_after = [
				'year'  => $from_date['year'],
				'month' => $from_date['month'],
				'day'   => $from_date['day'],
			];
		}

		// If --to-date option is provided.
		if ( ! empty( $assoc_args['to-date'] ) ) {

			$to_date = $this->validate_and_get_date( $assoc_args['to-date'] );

			if ( ! $to_date ) {
				$this->_error( 'Invalid value for --to-date! Ensure that the date is in dd-mm-yyyy format.' );
				return;
			}

			$posts_before = [
				'year'  => $to_date['year'],
				'month' => $to_date['month'],
				'day'   => $to_date['day'],
			];
		}

		// Check if date range is valid if both dates are provided.
		if ( ! empty( $from_date ) && ! empty( $to_date ) ) {

			if ( strtotime( $assoc_args['from-date'] ) > strtotime( $assoc_args['to-date'] ) ) {
				$this->_error( '--from-date should be lesser than or equal to --to-date' );
				return;
			}
		}

		// Write CSV headers.
		$csv_report_file = $this->write_to_csv(
			$csv_report_file,
			[],
			[
				[
					'Post Type',
					'Article ID',
					'Published Date',
					'Article URL',
					'Article Taxonomy ( Category + Sub Category )',
					'Article Taxonomy ( Vertical )',
					'Word Count',
					'Number of Image',
					'Attached Galleries',
				],
			]
		);

		$this->_write_log( sprintf( 'Writing CSV Report at: %s', $csv_report_file ), 0 );
		$this->_write_log( '', 0 );

		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true ); // @codeCoverageIgnore
		}

		$this->start_bulk_operation();

		// Iterate over post types.
		foreach ( $post_types as $post_type ) {

			$post_type = trim( $post_type );

			// Skip if post type is not supported.
			if ( ! in_array( $post_type, (array) $supported_post_types, true ) ) {

				$this->_write_log( sprintf( 'Skipped %s, post-type not supported!', $post_type ), 2 );
				continue;
			}

			$offset = 0;
			$args   = array(
				'post_type'        => $post_type,
				'post_status'      => 'publish',
				'suppress_filters' => false,
				'posts_per_page'   => $this->batch_size,
				'fields'           => 'ids',
				'date_query'       => [
					[
						'after'     => $posts_after,
						'before'    => $posts_before,
						'inclusive' => true,
					],
				],
			);

			do {

				$args['offset'] = $offset;
				$offset        += $this->batch_size;

				$posts_query = new \WP_Query( $args ); // phpcs:ignore
				$posts       = $posts_query->posts;

				if ( empty( $posts ) || ! is_array( $posts ) ) {
					break;
				}

				$report_data = [];

				foreach ( $posts as $post_id ) {

					$processed++;

					$post_permalink = get_permalink( $post_id );

					$this->_write_log( sprintf( '%d Processing Post ID: %d ( URL: %s )', $processed, $post_id, $post_permalink ), 0 );

					$this->_write_log( sprintf( ' - Post Date %s', get_the_date( '', $post_id ) ), 0 );

					// Get the word count.
					$word_count = get_post_meta( $post_id, '_pmc_word_count', true );
					$this->_write_log( sprintf( ' - Number of words: %d', $word_count ), 0 );

					// Get number of images in the post.
					$image_count = get_post_meta( $post_id, '_pmc_image_count', true );
					$this->_write_log( sprintf( ' - Number of images %d', $image_count ), 0 );

					// Get post categorization.
					$categorization = json_decode( get_post_meta( $post_id, '_pmc_post_categorization', true ) );

					if ( ! empty( $categorization ) ) {
						$categories = ! empty( $categorization->category ) ? $categorization->category : '';
						$verticals  = ! empty( $categorization->vertical ) ? $categorization->vertical : '';
					} else {
						$categories = '';
						$verticals  = '';
					}

					$this->_write_log( sprintf( ' - Taxonomy Categorization: ', $categories, $verticals ), 0 );
					$this->_write_log( sprintf( ' - Category: %s', $categories ), 0 );
					$this->_write_log( sprintf( ' - Vertical: %s', $verticals ), 0 );
					$this->_write_log( ' - ', 0 );

					// Get post linked gallery.
					$linked_gallery = get_post_meta( $post_id, 'pmc-gallery-linked-gallery', true );

					if ( ! empty( $linked_gallery ) ) {
						$linked_gallery = json_decode( $linked_gallery );
						$linked_gallery = $linked_gallery->url;
					} else {
						$linked_gallery = '';
					}

					$this->_write_log( sprintf( ' - Linked Gallery: %s ' . PHP_EOL, $linked_gallery ), 0 );

					// Note: the order is important here, it should correspond to the heads being written to CSV above.
					$report_row = [
						get_post_type( $post_id ),
						$post_id,
						get_the_date( '', $post_id ),
						$post_permalink,
						$categories,
						$verticals,
						$word_count,
						$image_count,
						$linked_gallery,
					];

					$report_data[] = $report_row;

					// Reset Data.
					unset( $report_row );

					// Call sleep() and stop_the_insanity() methods after every --max-iteration iteration.
					$this->_update_iteration();

				}

				// Write data to file if reporting is required.

				$this->write_to_csv(
					$csv_report_file,
					[],
					$report_data,
					null,
					'a'
				);

				// Reset data.
				$report_data = [];

			} while ( is_array( $posts ) && count( $posts ) === $this->batch_size && $this->batch_size > 0 );

		}

		$this->end_bulk_operation();

		$attachments = [];
		if ( ! empty( $csv_report_file ) ) {
			$attachments[] = $csv_report_file;
		}

		$this->_notify_done( sprintf( 'Processing Finished, Total Posts Processed: %d', $processed ), $attachments );

	}

	/**
	 * Helper function to validate and return an array containng date, month and year.
	 *
	 * @param string $date String containing date.
	 *
	 * @return string|array Returns an array containing date, month and year or empty string if date is not valid.
	 */
	private function validate_and_get_date( $date ) {

		if ( empty( $date ) || ! strpos( $date, '-' ) ) {
			return '';
		}

		$date = explode( '-', $date );

		if ( 3 !== count( $date ) ) {
			return '';
		}

		if ( ! checkdate( $date[1], $date[0], $date[2] ) ) {
			return '';
		}

		$format = [
			'day',
			'month',
			'year',
		];

		return array_combine( $format, $date );

	}

}

// Register the command.
\WP_CLI::add_command( 'pmc-editorial-reports', 'PMC\Editorial_Reports\WP_CLI\Editorial_Reports' ); // @codeCoverageIgnore
