<?php
namespace PMC\WP_CLI;

use PMC\Geo_Restricted_Content\Restrict_Image_Uses;

class Shutter_Stock extends \PMC_WP_CLI_Base {
	const COMMAND_NAME       = 'pmc-shutter-stock';
	const SINGLE_USE_POST_ID = 1;
	const SINGLE_USE_VALUE   = 'single_use';

	private $_csv_stream   = false;
	protected $_csv_file   = false;
	protected $_start_date = false;
	protected $_end_date   = false;

	protected $_assoc_args_properties_mapping = [
		'_csv_file'       => 'csv',
		'_start_date'     => 'start-date',
		'_end_date'       => 'end-date',
		'_caption_credit' => 'caption-credit',
	];

	/**
	 *
	 * WP-CLI command to delete meta key from post types
	 *
	 * ## OPTIONS
	 *
	 * --csv=<file>
	 * : The csv file to store the marked single use images
	 *
	 * [--caption-credit]
	 * : credit is stored in caption
	 *
	 * [--start-date=<YYYY-MM-DD>]
	 * : Optional filter images with a start date
	 *
	 * [--end-date=<YYYY-MM-DD>]
	 * : Optional filter images with an end date
	 *
	 * [--batch-size=<batch-size>]
	 * : The WP_Query posts_per_page argument you wish to set (number of batches)
	 *
	 * [--sleep=<sleep>]
	 * : The number of seconds to sleep between each batch
	 *
	 * [--disable-es-index]
	 * : Disable ES Indexing
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
	 *      # Mark scan and mark single use shutter stock images since Jan 1st, 2016
	 *      $ wp pmc-shutter-stock mark-single-use --csv=marked-log.csv --start-date=2016-01-01
	 *
	 * @subcommand mark-single-use
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function mark_single_use( array $args = [], array $assoc_args = [] ) : void {
		$this->_extract_common_args( $assoc_args );

		// IMPORTANT: Do not remove this call, must call to trigger disable es index & start_bulk_operation, etc...
		$this->_notify_start( 'Starting: Marking Shutter stock images as single use' );

		if ( ! empty( $this->_csv_file ) ) {
			$this->_csv_stream = fopen( $this->_csv_file, 'w' );  // phpcs:ignore
		}

		$offset = 0;
		$args   = array(
			'post_type'        => [ 'attachment', 'pmc-attachments' ],
			'post_status'      => [ 'publish', 'inherit' ],
			'suppress_filters' => false,
			'posts_per_page'   => abs( $this->batch_size ),
			'orderby'          => 'ID',
			'order'            => 'ASC',
		);

		if ( ! empty( $this->_start_date ) ) {
			$args['date_query']['after'] = gmdate( 'Y-m-d', strtotime( $this->_start_date ) );
		}
		if ( ! empty( $this->_end_date ) ) {
			$args['date_query']['before'] = gmdate( 'Y-m-d', strtotime( $this->_end_date ) );
		}
		if ( ! empty( $args['date_query'] ) ) {
			$args['date_query']['inclusive'] = true;
		}

		$processed_count = 0;

		do {

			// IMPORTANT: Keep these two statements here to avoid mistake/typo
			$args['offset'] = $offset;
			$offset        += $this->batch_size;

			$posts_query = new \WP_Query( $args ); // phpcs:ignore
			$posts       = $posts_query->posts;

			$found_posts = $posts_query->found_posts;

			if ( is_array( $posts ) ) {
				foreach ( $posts as $post ) {
					$mark_reason = false;
					$credit      = false;

					switch ( $post->post_type ) {
						case 'attachment':
							if ( $this->_caption_credit ) {
								$credit = $post->post_excerpt;
							} else {
								$credit = get_post_meta( $post->ID, '_image_credit', true );
							}
							if ( empty( $credit ) ) {
								$mark_reason = 'empty';
							} elseif ( $this->_is_shutter_stock( $credit ) ) {
								$mark_reason = $credit;
							}
							if ( ! empty( $mark_reason ) ) {
								$this->_mark_single_use( $post, $mark_reason );
							}
							break;
						case 'pmc-attachments':
							$credit = get_post_meta( $post->ID, 'image_credit', true );
							if ( $this->_is_shutter_stock( $credit ) ) {
								$mark_reason = sprintf( '%s from pmc-attachments id %d', $credit, $post->ID );
							}
							if ( ! empty( $mark_reason ) ) {
								// single use should be inherited by cloned image, therefore we need to mark the parent of the cloned image
								// This flag should not be override by clone, original image cannot be clone multiple times if image had been used
								if ( ! empty( $post->post_parent ) ) {
									$post_parent = get_post( $post->post_parent );
									if ( ! empty( $post_parent ) ) {
										$this->_mark_single_use( $post_parent, $mark_reason );
									} else {
										$this->_mark_single_use( $post, $mark_reason );
									}
								} else {
									$this->_mark_single_use( $post, $mark_reason );
								}
							}
							break;
					}

					if ( empty( $mark_reason ) ) {
						$this->_log_csv( $post->ID, $post->post_type, $credit, 'skipped' );
						$this->_write_log( sprintf( 'Not mark: %d, %s', $post->ID, $credit ) );
					}

				} // foreach

				// cleanup resources for each page
				$this->stop_the_insanity();
				$processed_count += count( $posts );
			}

			$this->_write_log( sprintf( 'Processed %d posts from total %d', $processed_count, $found_posts ) );

		} while ( is_array( $posts ) && count( $posts ) === $this->batch_size );

		if ( ! empty( $this->_csv_stream ) ) {
			fclose( $this->_csv_stream ); // phpcs:ignore
		}

		$attachments = [
			$this->_csv_file,
		];

		$this->_notify_done( 'Process completed.', $attachments );
	}

	private function _mark_single_use( $post, string $mark_reason ) : void {
		$post = get_post( $post );
		if ( empty( $post ) || ! in_array( $post->post_type, [ 'attachment', 'pmc-attachments' ], true ) ) {
			return;
		}
		$changed     = false;
		$single_used = get_post_meta( $post->ID, Restrict_Image_Uses::META_IMAGE_SINGLE_USED, true );
		if ( empty( $single_used ) ) {
			if ( $this->dry_run ) {
				$this->_write_log( sprintf( 'Dry run: %d, would add post meta %s => %s', $post->ID, Restrict_Image_Uses::META_IMAGE_SINGLE_USED, self::SINGLE_USE_POST_ID ) );
			} else {
				update_post_meta( $post->ID, Restrict_Image_Uses::META_IMAGE_SINGLE_USED, self::SINGLE_USE_POST_ID );
				$changed = true;
			}
		}
		$restrict_type = get_post_meta( $post->ID, Restrict_Image_Uses::META_IMAGE_RESTRICTED_TYPE, true );
		if ( self::SINGLE_USE_VALUE !== $restrict_type ) {
			if ( $this->dry_run ) {
				$this->_write_log( sprintf( 'Dry run: %d, would add post meta %s => %s', $post->ID, Restrict_Image_Uses::META_IMAGE_RESTRICTED_TYPE, self::SINGLE_USE_VALUE ) );
			} else {
				update_post_meta( $post->ID, Restrict_Image_Uses::META_IMAGE_RESTRICTED_TYPE, self::SINGLE_USE_VALUE );
				$changed = true;
			}
		}

		if ( ! $this->dry_run ) {
			if ( $changed ) {
				$this->_log_csv( $post->ID, $post->post_type, $mark_reason );
				$this->_write_log( sprintf( 'Marked: %d, %s, %s', $post->ID, $post->post_type, $mark_reason ) );
			} else {
				$this->_log_csv( $post->ID, $post->post_type, $mark_reason, 'exists' );
				$this->_write_log( sprintf( 'Exists: %d, %s', $post->ID, $post->post_type ) );
			}
		}

		// passive update the iteration counter to throttle the process and calling stop_the_insanity
		$this->_update_iteration();
	}

	private function _log_csv( int $id, string $type, string $reason, string $status = 'marked' ) : void {
		if ( empty( $this->_csv_stream ) ) {
			return;
		}
		$row = [ $id, $status, $type, $reason ];
		fputcsv( $this->_csv_stream, $row );  // phpcs:ignore
	}

	private function _is_shutter_stock( string $credit ) : bool {
		$credit = strtolower( trim( $credit ) );
		if ( in_array( $credit, [ 'shutterstock', 'shutter stock' ], true ) ) {
			return true;
		}

		$credit = str_replace( [ ';', ',' ], '/', $credit );
		$tokens = explode( '/', $credit );
		$tokens = array_map(
			function( $item ) {
				return trim( $item, ' ,;' );
			},
			(array) $tokens
		);
		$string = '#' . implode( '#', $tokens ) . '#';
		if ( preg_match( '/#(shutterstock|shutter stock)#/', $string ) ) {
			if ( preg_match( '/#(variety|deadline|rolling stone|hollywood life|indiewire|footwear news|tvline|artnews)#/', $string ) ) {
				// PMC may have the copyright on
				return false;
			}
			return true;
		}

		// Capture all
		if ( preg_match( '/shutterstock|shutter stock/', $credit ) ) {
			return true;
		}

		return false;
	}

	/**
	 *
	 * WP-CLI command to delete meta key from post types
	 *
	 * ## OPTIONS
	 *
	 * --csv=<file>
	 * : The csv file to store the marked single use images
	 *
	 * [--caption-credit]
	 * : credit is stored in caption
	 *
	 * [--start-date=<YYYY-MM-DD>]
	 * : Optional filter images with a start date
	 *
	 * [--end-date=<YYYY-MM-DD>]
	 * : Optional filter images with an end date
	 *
	 * [--batch-size=<batch-size>]
	 * : The WP_Query posts_per_page argument you wish to set (number of batches)
	 *
	 * [--sleep=<sleep>]
	 * : The number of seconds to sleep between each batch
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
	 *      # Generate single use report
	 *      $ wp pmc-shutter-stock generate-report --csv=report-log.csv
	 *
	 * @subcommand generate-report
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function generate_report( array $args = [], array $assoc_args = [] ) : void {
		$this->_extract_common_args( $assoc_args );
		$this->dry_run = false;

		// IMPORTANT: Do not remove this call, must call to trigger disable es index & start_bulk_operation, etc...
		$this->_notify_start( 'Starting: Generate single use report' );

		$this->_csv_stream = fopen( $this->_csv_file, 'w' );  // phpcs:ignore
		$headers           = [
			'ID',
			'status',
			'post type',
			'post date',
			'credit/reason',
		];
		fputcsv( $this->_csv_stream, $headers );  // phpcs:ignore

		$offset = 0;
		$args   = array(
			'post_type'        => [ 'attachment', 'pmc-attachments' ],
			'post_status'      => [ 'publish', 'inherit' ],
			'suppress_filters' => false,
			'posts_per_page'   => abs( $this->batch_size ),
			'orderby'          => 'ID',
			'order'            => 'ASC',
		);

		if ( ! empty( $this->_start_date ) ) {
			$args['date_query']['after'] = gmdate( 'Y-m-d', strtotime( $this->_start_date ) );
		}
		if ( ! empty( $this->_end_date ) ) {
			$args['date_query']['before'] = gmdate( 'Y-m-d', strtotime( $this->_end_date ) );
		}
		if ( ! empty( $args['date_query'] ) ) {
			$args['date_query']['inclusive'] = true;
		}

		$processed_count = 0;

		do {

			// IMPORTANT: Keep these two statements here to avoid mistake/typo
			$args['offset'] = $offset;
			$offset        += $this->batch_size;

			$posts_query = new \WP_Query( $args ); // phpcs:ignore
			$posts       = $posts_query->posts;

			$found_posts = $posts_query->found_posts;

			if ( is_array( $posts ) ) {
				foreach ( $posts as $post ) {
					$credit = false;

					if ( $this->_caption_credit ) {
						$credit = $post->post_excerpt;
					} else {
						switch ( $post->post_type ) {
							case 'attachment':
								$credit = get_post_meta( $post->ID, '_image_credit', true );
								break;
							case 'pmc-attachments':
								$credit = get_post_meta( $post->ID, 'image_credit', true );
								break;
						}
					}

					$restrict_type = get_post_meta( $post->ID, Restrict_Image_Uses::META_IMAGE_RESTRICTED_TYPE, true );
					$row           = [
						$post->ID,
						$restrict_type,
						$post->post_type,
						$post->post_date,
						$credit,
					];
					fputcsv( $this->_csv_stream, $row );  // phpcs:ignore

				} // foreach

				// cleanup resources for each page
				$this->stop_the_insanity();
				$processed_count += count( $posts );
			}

			$this->_write_log( sprintf( 'Processed %d posts from total %d', $processed_count, $found_posts ) );

		} while ( is_array( $posts ) && count( $posts ) === $this->batch_size );

		if ( ! empty( $this->_csv_stream ) ) {
			fclose( $this->_csv_stream ); // phpcs:ignore
		}

		$attachments = [
			$this->_csv_file,
		];

		$this->_notify_done( 'Process completed.', $attachments );
	}

}
