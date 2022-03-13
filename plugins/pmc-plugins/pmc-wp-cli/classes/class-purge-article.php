<?php
namespace PMC\WP_CLI;

class Purge_Article extends \PMC_WP_CLI_Base {
	protected $_sitemap_rebuild_queues = [];

	/**
	 * Migrate into legacy redirector
	 *
	 * @subcommand purge
	 *
	 * ## OPTIONS
	 *
	 * [--csv=<file>]
	 * : The file containing the list of rules to migrate
	 *
	 * [--all]
	 * : If csv not pass, migrate all entries
	 *
	 * [--dry-run]
	 * : Do dry run
	 *
	 * --log-file=<log-file>
	 * : Path/Filename to the log file
	 *
	 * [--post-type]
	 * : Do operation for post types other than default post.
	 *
	 * ## EXAMPLES
	 *
	 *    wp pmc-purge-article purge --csv=migrate.csv --log-file=/var/log/pmc-safe-redirect-manager-delete-all.log
	 *
	 */
	public function purge( $args = array(), $assoc_args = array() ) {

		$this->max_iteration = 99;  // number of iteration before calling sleep if requested

		$this->_extract_common_args( $assoc_args );

		$csv = ( ! empty( $assoc_args['csv'] ) ) ? $assoc_args['csv'] : false;
		$all = ( isset( $assoc_args['all'] ) );

		if ( ! empty( $csv ) ) {

			// Disable after-post-update actions
			if ( ! defined( 'WP_IMPORTING' ) ) {
				define( 'WP_IMPORTING', true ); // @codeCoverageIgnore
			}

			$this->start_bulk_operation();

			$this->_notify_start( 'Starting: Purging articles listed in CSV file' );

			$row    = 0;
			$handle = fopen( $csv, 'r' ); // phpcs:ignore
			if ( false !== $handle ) {

				$headers = fgetcsv( $handle, 2000, ',' );
				$headers = array_map( 'strtolower', (array) $headers );

				if ( ! in_array( 'id', (array) $headers, true ) ) {
					$this->_error( 'CSV must have a header row with column name "id"' );
				}

				$row             = 1;
				$posts_to_modify = [];

				while ( false !== ( $data = fgetcsv( $handle, 2000, ',' ) ) ) { // phpcs:ignore
					$row++;

					if ( count( $headers ) !== count( $data ) ) {
						$this->_warning( sprintf( 'Skipped invalid csv entry on row %1$d', $row ) );
						continue;
					}

					$data = array_combine( $headers, $data );

					$posts_to_modify[ $data['id'] ] = $data;

					// batch up 100 posts
					if ( count( $posts_to_modify ) < 100 ) {
						continue;
					}

					// Ignoring code coverage Since test cases don't have 100 posts, code below this won't be executed for test cases.
					// Process the batch to handle redirects.
					$this->_process_posts( $posts_to_modify, $assoc_args['post-type'] ); // @codeCoverageIgnore

					// empty the bulk array.
					$posts_to_modify = []; // @codeCoverageIgnore

				} // while

				// Need to process the last batch as it might be unprocessed if it was less than 100.
				if ( ! empty( $posts_to_modify ) ) {
					$this->_process_posts( $posts_to_modify, $assoc_args['post-type'] );
					$posts_to_modify = [];
				}

				// Reset the iteration count in base class.
				$this->_iteraction_count = 0;

				fclose( $handle ); // phpcs:ignore

				if ( ! empty( $this->_sitemap_rebuild_queues ) ) {
					foreach ( $this->_sitemap_rebuild_queues as $item ) {

						if ( $this->dry_run ) {
							$this->_write_log( sprintf( 'Would trigger sitemap to rebuild: %s-%s%s', $item['type'], $item['year'], $item['month'] ) );
						} else {
							$sitemap = \PMC_Sitemaps::get_instance()->trigger_rebuild( $item['type'], $item['year'], $item['month'] );
							if ( $sitemap ) {
								$this->_write_log( sprintf( 'Triggered sitemap id=%d to rebuild: %s-%s%s', $sitemap->ID, $item['type'], $item['year'], $item['month'] ) );
							} else {
								$this->_warning( sprintf( 'Failed to trigger sitemap rebuild: %s-%s%s', $item['type'], $item['year'], $item['month'] ) );
							}
						}

						// call _update_iteration only after batch of 100.
						if ( ! $this->dry_run ) {
							// This code take care of calling stop_the_insanity and sleep
							$this->_update_iteration();
						}

					} // for
				}

			}

		}

		$this->end_bulk_operation();
		$this->_notify_done();

	}

	/**
	 * Helper method for handling redirects, sitemap.
	 *
	 * @param array  $posts_to_modify Batch of the posts to redirect.
	 * @param string $post_type       name of post type which needs to be operated.
	 */
	private function _process_posts( $posts_to_modify, $post_type = 'post' ) {
		// we have 100 posts!
		$posts = get_posts( // phpcs:ignore
			[
				// I'm scared of off by one errors
				'posts_per_page'      => 101, // phpcs:ignore
				'include'             => wp_list_pluck( $posts_to_modify, 'id' ),
				'ignore_sticky_posts' => true,
				'post_type'           => $post_type,
				'suppress_filters'    => false,
			]
		);

		foreach ( $posts as $post ) {

			$data = $posts_to_modify[ $post->ID ];

			$permalink     = get_permalink( $post );
			$redirect_to   = ( ! empty( $data['redirect_to'] ) ) ? $data['redirect_to'] : false;
			$redirect_from = ( ! empty( $data['redirect_from'] ) ) ? $data['redirect_from'] : false;

			$type     = $post->post_type;
			$tmp_date = strtotime( $post->post_date_gmt );
			$year     = date( 'Y', $tmp_date );
			$month    = date( 'm', $tmp_date );

			if ( ! isset( $this->_sitemap_rebuild_queues[ $type . $year . $month ] ) ) {
				$this->_sitemap_rebuild_queues[ $type . $year . $month ] = [
					'type'  => $type,
					'year'  => $year,
					'month' => $month,
				];
				$this->_write_log( sprintf( 'Queued for sitemap rebuild: %s-%s%s', $type, $year, $month ) );
			}

			if ( $this->dry_run ) {
				if ( ! empty( $redirect_to ) ) {
					$this->_write_log( sprintf( 'Would add to legacy redirector from %s to %s', $permalink, $redirect_to ) );
					if ( ! empty( $redirect_from ) && $permalink !== $redirect_from ) {
						$this->_write_log( sprintf( 'Would add to legacy redirector from %s to %s', $redirect_from, $redirect_to ) );
					}
				}
				$this->_write_log( sprintf( 'Would delete post id = %d', $post->ID ) );
			} else {
				if ( ! empty( $redirect_to ) ) {
					\WPCOM_Legacy_Redirector::insert_legacy_redirect( $permalink, $redirect_to );
					$this->_write_log( sprintf( 'Added to legacy redirector from %s to %s', $permalink, $redirect_to ) );
					if ( ! empty( $redirect_from ) && $permalink !== $redirect_from ) {
						\WPCOM_Legacy_Redirector::insert_legacy_redirect( $redirect_from, $redirect_to );
						$this->_write_log( sprintf( 'Added to legacy redirector from %s to %s', $redirect_from, $redirect_to ) );
					}
				}
				wp_delete_post( $post->ID );
				$this->_write_log( sprintf( 'Deleted post id = %d', $post->ID ) );
			}

			if ( ! $this->dry_run ) {
				// This code take care of calling stop_the_insanity and sleep
				$this->_update_iteration();
			}

		} // foreach $posts

	}

} //end class

//EOF
