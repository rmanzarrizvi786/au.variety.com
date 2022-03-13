<?php
/**
 * WP-CLI command to manage meta
 *
 * @version 2017-08-25 CDWE-587
 *
 * @author Chandra Patel <chandrakumar.patel@rtcamp.com>
 *
 * @package pmc-wp-cli
 */

namespace PMC\WP_CLI;

class Meta extends \PMC_WP_CLI_Base {

	const COMMAND_NAME = 'pmc-meta';

	/**
	 *
	 * WP-CLI command to delete meta key from post types
	 *
	 * ## OPTIONS
	 *
	 * <meta-key>
	 * : Meta key to delete
	 *
	 * [--post_type]
	 * : Post type from which meta key will delete
	 * ---
	 * default: post
	 * options:
	 *   - post -- Default post type
	 *   - any -- Meta key will delete from all public post types except revisions
	 *   - all -- Meta key will delete from all post types including public and private post types
	 *   - Any custom post type or multiple post types with comma separated
	 *
	 * [--dry-run]
	 * : Whether or not to do dry run
	 * ---
	 * default: false
	 * options:
	 *   - true
	 *   - false
	 *
	 * [--log-file=<file>]
	 * : Path to the log file.
	 *
	 * [--email=<email>]
	 * : Email to send notification after script complete.
	 *
	 * [--email-when-done]
	 * : Whether to send notification or not.
	 *
	 * [--email-logfile]
	 * : Whether to send log file or not.
	 *
	 * ## EXAMPLES
	 *
	 *      # Delete meta_key from post
	 *      $ wp pmc-meta delete-key meta_key
	 *
	 *      # Delete meta_key from post and pmc-gallery post types
	 *      wp pmc-meta delete-key meta_key --post_type=post,pmc-gallery
	 *
	 *      # Delete meta_key from any post types
	 *      wp pmc-meta delete-key meta_key --post_type=any
	 *
	 *      # Delete meta_key from public post types
	 *      wp pmc-meta delete-key meta_key --post_type=public
	 *
	 * @subcommand delete-key
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function delete_key( $args = array(), $assoc_args = array() ) {

		$this->_extract_common_args( $assoc_args );

		if ( $this->dry_run ) {
			$this->_warning( 'You have called the command pmc-meta:delete-key in dry run mode.' . "\n" );
		}

		if ( empty( $args[0] ) ) {
			$this->_error( 'Please pass meta key to delete.' );
		}

		$meta_key = sanitize_key( $args[0] );

		if ( ! empty( $assoc_args['post_type'] ) && is_string( $assoc_args['post_type'] ) ) {

			if ( 'any' === $assoc_args['post_type'] ) {

				$post_type = 'any';

			} elseif ( 'all' === $assoc_args['post_type'] ) {

				$post_type = 'all';

			} else {

				$post_type = explode( ',', $assoc_args['post_type'] );

				$post_type = array_map( 'sanitize_title', $post_type );

			}

		} else {
			$post_type = array( 'post' );
		}

		if ( 'all' === $post_type ) {

			$total_deleted = $this->_delete_post_meta_by_key( $meta_key );

			if ( false === $total_deleted ) {
				$this->_write_log( sprintf( '%s meta key not deleted', $meta_key ) );
			} else {
				$this->_write_log( sprintf( '%s meta key deleted from %d posts', $meta_key, $total_deleted ) );
			}

		} else {

			$total_posts_affected = $this->_delete_meta_key_from_post_types( $meta_key, $post_type );

			if ( false === $total_posts_affected ) {
				$this->_write_log( sprintf( '%s meta key not deleted from all posts', $meta_key ) );
			} else {
				$this->_write_log( sprintf( '%s meta key deleted from %d posts', $meta_key, $total_posts_affected ) );
			}

		}

		$this->stop_the_insanity();

		$this->_notify_done( 'WP-CLI command pmc-meta:delete-key Completed' );

	}

	/**
	 * Delete post meta by key
	 *
	 * @param string $meta_key A meta key.
	 *
	 * @return bool|int
	 */
	protected function _delete_post_meta_by_key( $meta_key ) {

		global $wpdb;

		if ( empty( $meta_key ) ) {
			return false;
		}

		$limit         = 50;
		$total_deleted = 0;
		$all_done      = false;
		$offset        = 0;
		$iteration     = 0;

		$select_post_meta_sql = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s LIMIT %d, %d";

		while ( false === $all_done ) {

			if ( $this->dry_run ) {
				$offset = ( $iteration * $limit );
			}

			$post_ids = $wpdb->get_col( $wpdb->prepare( $select_post_meta_sql, $meta_key, $offset, $limit ) );

			if ( empty( $post_ids ) ) {
				$all_done = true;
				break;
			}

			$total_posts = count( $post_ids );

			for ( $i = 0; $i < $total_posts; $i++ ) {

				if ( ! $this->dry_run ) {
					delete_post_meta( $post_ids[ $i ], $meta_key );
				}

				$this->_write_log( sprintf( '%s meta key deleted from post %d', $meta_key, $post_ids[ $i ] ) );

				$total_deleted++;

			}

			$iteration++;

			// To prevent execution of command due to memory issue.
			$this->stop_the_insanity();

			sleep( 2 );

		}

		return $total_deleted;

	}

	/**
	 * Delete meta key from post type
	 *
	 * @param string       $meta_key  A meta key.
	 * @param string|array $post_type An array of post types or single post type.
	 *
	 * @return bool|int
	 */
	protected function _delete_meta_key_from_post_types( $meta_key, $post_type ) {

		if ( empty( $meta_key ) || empty( $post_type ) ) {
			return false;
		}

		$limit = 100;

		$total_posts_affected = 0;

		$args = array(
			'post_type'        => $post_type,
			'posts_per_page'   => $limit,
			'paged'            => 1,
			'suppress_filters' => false,
		);

		do {

			$posts = get_posts( $args );

			if ( empty( $posts ) ) {
				break;
			}

			$total_posts = count( $posts );

			for ( $i = 0; $i < $total_posts; $i++ ) {

				$post = $posts[ $i ];

				$this->_write_log( sprintf( 'Post Detail: %d | %s | %s', $post->ID, $post->post_type, $post->post_title ) );

				if ( ! $this->dry_run ) {
					delete_post_meta( $post->ID, $meta_key );
				}

				$this->_write_log( sprintf( '%s meta key deleted from post %d', $meta_key, $post->ID ) );

				$this->_write_log( '' ); // To add blank line.

				$total_posts_affected++;

				unset( $post );

			}

			$args['paged']++;

			// To prevent execution of command due to memory issue.
			$this->stop_the_insanity();

			sleep( 2 );

		} while ( $total_posts === $limit );

		return $total_posts_affected;

	}

	/**
	 *
	 * WP-CLI command to generate a csv report of meta key usage for each post type
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Dry run
	 *
	 * [--batch-size=<batch-size>]
	 * : The query batch size to limit number of result return at one time
	 *
	 * [--csv=<file>]
	 * : Path to generate the csv file report.
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
	 *
	 * @subcommand stats
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 *
	 * @codeCoverageIgnore There is no need to do any code coverage for this script.  Script just generating a report
	 */
	public function stats( $args = array(), $assoc_args = array() ) {
		global $wpdb;

		$attachments = [];

		$this->_extract_common_args( $assoc_args );
		$this->_notify_start( 'Generating data' );

		if ( ! empty( $assoc_args['csv'] ) ) {
			$fp = fopen( $assoc_args['csv'], 'w' ); // phpcs:ignore
			$attachments[] = $assoc_args['csv'];
		} else {
			$fp = fopen( 'php://stdout', 'w'); // phpcs:ignore
		}

		// phpcs:ignore
		fputcsv( $fp,
			[
				'post_type',
				'meta_key',
				'count',
				'registered',
			]
		);

		// We need to use direct query to run a report with group post type & meta key
		$sql = "
				SELECT p.post_type,m.meta_key,count(*) as c
				FROM {$wpdb->posts} as p
				JOIN {$wpdb->postmeta} as m
				ON p.ID = m.post_id
				AND p.post_status = 'publish'
				AND p.post_type not in ('vip-legacy-redirect','revision')
				AND m.meta_key not in ( '_edit_lock', '_edit_last', '_thumbnail_id', '_wp_old_date', '_wp_old_slug', '_pingme', '_encloseme' )
				AND m.meta_key not like '#_oembed%' ESCAPE '#'
				AND m.meta_key not like 'sailthru#_breaking#_news#_alert#_logs%' ESCAPE '#'
				GROUP BY p.post_type,m.meta_key
				HAVING count(*) > 1
				ORDER BY c DESC, p.post_type,m.meta_key
				LIMIT %d,%d";

		$this->start_bulk_operation();
		$offset = 0;
		do {

			$results = $wpdb->get_results( $wpdb->prepare( $sql, $offset, $this->batch_size ), ARRAY_A ); // phpcs:ignore
			$offset += $this->batch_size;

			if ( ! empty( $results ) && count( $results ) > 0 ) {
				foreach ( $results as $row ) {
					$row['registered'] = registered_meta_key_exists( 'post', $row['meta_key'], $row['post_type'] ) ? 'registered' : '';
					fputcsv( $fp, $row ); // phpcs:ignore
				}
			}

			if ( count( $results ) === $this->batch_size ) {
				$this->stop_the_insanity();
				sleep( $this->sleep );
			}

			if ( $this->dry_run ) {
				break;
			}

		} while ( ! empty( $results ) && count( $results ) === $this->batch_size );
		$this->end_bulk_operation();

		fclose( $fp ); // phpcs:ignore

		$this->_notify_done( 'WP CLI Script Completed', $attachments );
	}

	/**
	 * WP-CLI command to remove incomplete structured data from featured video override meta field.
	 *
	 * ## OPTIONS
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
	 * [--offset]
	 * : Offset value for query.
	 * ---
	 * default: 0
	 * ---
	 *
	 * [--dry-run]
	 * : Whether or not to do dry run
	 * ---
	 * default: false
	 * options:
	 *   - true
	 *   - false
	 *
	 * [--log-file=<file>]
	 * : Path to the log file.
	 *
	 * [--email=<email>]
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
	 *      $ wp pmc-meta remove-featured-video-incomplete-structured-data --dry-run=false --log-file=./log.txt --csv-report-file=./report.csv
	 *
	 * @subcommand remove-featured-video-incomplete-structured-data
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 *
	 * @codeCoverageIgnore There is no need to do any code coverage for this, it's a one time script.
	 */
	public function remove_incomplete_structured_data( $args = array(), $assoc_args = array() ) {

		$this->_extract_common_args( $assoc_args );

		if ( $this->dry_run ) {
			$this->_warning( 'You have called the command pmc-meta:remove-featured-video-incomplete-structured-data in dry run mode.' . "\n" );
		}

		$csv_report_file = ( ! empty( $assoc_args['csv-report-file'] ) ) ? $assoc_args['csv-report-file'] : false;

		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		$processed = 0;
		$updated   = 0;

		// Write CSV headers.
		if ( ! empty( $csv_report_file ) ) {
			$csv_report_file = $this->write_to_csv(
				$csv_report_file,
				[],
				[
					[
						'Article ID',
						'Post Type',
						'Article Title',
						'Article URL',
						'Original Post Meta',
						'Updated Post Meta',
					],
				]
			);

			$this->_write_log( sprintf( 'Writing CSV Report at: %s', $csv_report_file ), 0 );
			$this->_write_log( '', 0 );
		}

		$this->start_bulk_operation();

		$offset = ( ! empty( $assoc_args['offset'] ) && intval( $assoc_args['offset'] ) > 0 ) ? intval( $assoc_args['offset'] ) : 0;
		$args   = array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'suppress_filters' => false,
			'posts_per_page'   => $this->batch_size,
			'fields'           => 'ids',
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'meta_query'       => array(                           // phpcs:ignore slow query ok.
				'key'     => '_pmc_featured_video_override_data',
				'compare' => 'EXISTS',
			),
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
				$permalink               = get_permalink( $post_id );
				$post_title              = get_the_title( $post_id );
				$featured_video_override = get_post_meta( $post_id, '_pmc_featured_video_override_data', true );
				$pattern                 = '/(?:<iframe[^>]*(?:theplatform)[^>]*)(?:(?:\/>)|(?:>.*?<\/iframe>))/m'; // Grab iframes with theplatform used in source.
				$matches                 = array();

				preg_match_all( $pattern, $featured_video_override, $matches, PREG_SET_ORDER, 0 );

				if ( ! empty( $matches[0][0] ) ) {

					$updated++;
					$updated_meta = $matches[0][0];

					if ( ! $this->dry_run ) {

						update_post_meta( $post_id, '_pmc_featured_video_override_data', $updated_meta ); // Update post meta with only iframe, removes other unnecessary markup.
						update_post_meta( $post_id, '_pmc_featured_video_override_data_backup', $featured_video_override ); // Save a backup copy incase we need it later.

						$this->_write_log( sprintf( 'Removed incomplete structured data from: %s ( %s )' . PHP_EOL, $post_title, $permalink ) );

					} else {

						$this->_write_log( sprintf( '%d. Will Remove incomplete structured data from: %s ( %s ), replace with new value: %s' . PHP_EOL, $processed, $post_title, $permalink, $updated_meta ) );
					}

					$report_row = array(
						$post_id,
						get_post_type( $post_id ),
						$post_title,
						$permalink,
						$featured_video_override,
						$updated_meta,
					);

					$report_data[] = $report_row;

					// Reset report row data.
					unset( $report_row );

				} else {

					$this->_write_log( sprintf( '%d. Skipped: %s ( %s )' . PHP_EOL, $processed, $post_title, $permalink ) );
				}

				// Call sleep() and stop_the_insanity() methods after every --max-iteration iteration.
				$this->_update_iteration();
			}

			// Write data to file if reporting is required.
			if ( ! empty( $report_data ) ) {

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

		} while ( is_array( $posts ) && ( count( $posts ) === $this->batch_size ) && ( $this->batch_size > 0 ) );

		$this->end_bulk_operation();

		$this->_notify_done( sprintf( 'WP-CLI command pmc-meta:remove-featured-video-incomplete-structured-data Completed, Total %d posts processed, Total %d posts updated.', $processed, $updated ) );
	}

	/**
	 * WP-CLI command to remove thePlatform featured videos.
	 *
	 * ## OPTIONS
	 *
	 * default: false
	 * options:
	 *   - true
	 *   - false
	 * ---
	 *
	 * [--offset]
	 * : Offset value for query.
	 * ---
	 * default: 0
	 * ---
	 *
	 * [--dry-run]
	 * : Whether or not to do dry run
	 * ---
	 * default: false
	 * options:
	 *   - true
	 *   - false
	 *
	 * [--log-file=<file>]
	 * : Path to the log file.
	 *
	 * [--email=<email>]
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
	 *      $ wp pmc-meta remove-theplatform-featured-videos --dry-run=true --log-file=./log.txt
	 *
	 * @subcommand remove-theplatform-featured-videos
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 *
	 */
	public function remove_theplatform_featured_videos( $args = array(), $assoc_args = array() ) {

		$this->_extract_common_args( $assoc_args );

		if ( $this->dry_run ) {
			$this->_warning( 'You have called the command pmc-meta:remove-theplatform-featured-videos in dry run mode.' . "\n" );
		}

		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		$processed = 0;
		$updated   = 0;

		$this->start_bulk_operation();

		$offset = ( ! empty( $assoc_args['offset'] ) && intval( $assoc_args['offset'] ) > 0 ) ? intval( $assoc_args['offset'] ) : 0;
		$args   = array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'suppress_filters' => false,
			'posts_per_page'   => $this->batch_size,
			'fields'           => 'ids',
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'meta_query'       => [                           // phpcs:ignore slow query ok.
				[
					'key'     => '_pmc_featured_video_override_data',
					'compare' => 'EXISTS',
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

			foreach ( $posts as $post_id ) {

				$processed++;
				$permalink               = get_permalink( $post_id );
				$post_title              = get_the_title( $post_id );
				$featured_video_override = get_post_meta( $post_id, '_pmc_featured_video_override_data', true );

				if ( strpos( $featured_video_override, 'player.theplatform.com' ) !== false ) {

					$updated++;

					preg_match( '/src="([^"]+)"/', $featured_video_override, $match );
					$src_url = $match[1];

					if ( ! $this->dry_run ) {

						delete_post_meta( $post_id, '_pmc_featured_video_override_data' );

						$this->_write_log( sprintf( 'Removed thePlatform video from: %s, %s ( %s ), Video: %s' . PHP_EOL, $post_id, $post_title, $permalink, $src_url ) );

					} else {

						$this->_write_log( sprintf( '%d. Will Remove thePlatform video from: %s, %s ( %s ), Video: %s' . PHP_EOL, $processed, $post_id, $post_title, $permalink, $src_url ) );
					}

				} else {

					$this->_write_log( sprintf( '%d. Skipped: %s, %s ( %s )' . PHP_EOL, $processed, $post_id, $post_title, $permalink ) );
				}

				// Call sleep() and stop_the_insanity() methods after every --max-iteration iteration.
				$this->_update_iteration();
			}

		} while ( is_array( $posts ) && ( count( $posts ) === $this->batch_size ) && ( $this->batch_size > 0 ) );

		$this->end_bulk_operation();

		$this->_notify_done( sprintf( 'WP-CLI command pmc-meta:remove-theplatform-featured-videos Completed, Total %d posts processed, Total %d posts updated.', $processed, $updated ) );
	}

}

//EOF
