<?php
namespace PMC\WP_CLI;

use PMC\Geo_Restricted_Content\Restrict_Image_Uses;

class Associated_Press extends \PMC_WP_CLI_Base {
	const COMMAND_NAME       = 'pmc-associated-press';
	const SINGLE_USE_POST_ID = 1;
	const SINGLE_USE_VALUE   = 'single_use';

	private $_csv_stream   = false;
	protected $_csv_file   = false;
	protected $_start_date = false;
	protected $_end_date   = false;

	protected $_assoc_args_properties_mapping = [
		'_csv_file'   => 'csv',
		'_start_date' => 'start-date',
		'_end_date'   => 'end-date',
	];

	/**
	 *
	 * WP-CLI command to mark AP images as single use
	 *
	 * ## OPTIONS
	 *
	 * --csv=<file>
	 * : The csv file to store the marked single use images
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
	 *      # Mark scan and mark single use Associated Press images since July 1st, 2020
	 *      $ wp pmc-associated-press mark-single-use --csv=marked-log.csv --start-date=2020-07-01
	 *
	 * @subcommand mark-single-use
	 *
	 * @param array $args Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function mark_single_use( array $args = [], array $assoc_args = [] ) : void {
		$this->_extract_common_args( $assoc_args );

		// IMPORTANT: Do not remove this call, must call to trigger disable es index & start_bulk_operation, etc...
		$this->_notify_start( 'Starting: Marking Associated Press images as single use' );

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

			$args['offset'] = $offset;
			$offset        += $this->batch_size;

			$posts_query = new \WP_Query( $args ); // phpcs:ignore
			$posts       = $posts_query->posts;

			$found_posts = $posts_query->found_posts;

			if ( is_array( $posts ) ) {

				foreach ( $posts as $post ) {

					switch ( $post->post_type ) {
						case 'attachment':
							$credit = get_post_meta( $post->ID, '_image_credit', true );

							if ( $this->_is_AP_image( $post->post_excerpt ) || $this->_is_AP_image( $credit ) ) {
								$this->_mark_single_use( $post );
							}

							break;
						case 'pmc-attachments':
							$credit = get_post_meta( $post->ID, '_image_credit', true );

							if ( $this->_is_AP_image( $credit ) ) {

								// single use should be inherited by cloned image, therefore we need to mark the parent of the cloned image
								// This flag should not be override by clone, original image cannot be clone multiple times if image had been used
								if ( ! empty( $post->post_parent ) ) {

									$post_parent = get_post( $post->post_parent );

									if ( ! empty( $post_parent ) ) {
										$this->_mark_single_use( $post_parent );
									} else {
										$this->_mark_single_use( $post );
									}

								} else {
									$this->_mark_single_use( $post );
								}

							}

							break;
					}
				} // foreach

				// cleanup resources for each page
				$this->stop_the_insanity();
				$processed_count += count( $posts );
				$this->_write_log( sprintf( 'Processed %d posts from total %d', $processed_count, $found_posts ) );
			}

		} while ( is_array( $posts ) && count( $posts ) === $this->batch_size );

		if ( ! empty( $this->_csv_stream ) ) {
			fclose( $this->_csv_stream ); // phpcs:ignore
		}

		$attachments = [
			$this->_csv_file,
		];

		$this->_notify_done( 'Process completed.', $attachments );
	}

	/**
	 * check if the credit is variation of Associated press image
	 * @param string $credit
	 *
	 * @return bool
	 */
	private function _is_AP_image( string $credit ) : bool {

		$credit = strtolower( trim( $credit ) );
		if ( in_array( $credit, [ '/ap', 'ap', 'associated press', 'wrt ap', 'via ap', 'ap images' ], true ) ) {
			return true;
		}

		$matches = preg_grep( '/\bap\b|\bassociated press\b/i', [ $credit ] );
		if ( ! empty( $matches ) && is_array( $matches ) ) {
			return true;
		}
		return false;
	}

	/**
	 * @param $post
	 *
	 */
	private function _mark_single_use( $post ) : void {
		$changed = false;
		// check & skip if it is already flagged
		$meta = get_post_meta( $post->ID, Restrict_Image_Uses::META_IMAGE_RESTRICTED_TYPE, true );

		if ( 'single_use' !== $meta ) {

			if ( $this->dry_run ) {
				$this->_write_log( sprintf( 'Dry run: %d, would add post meta', $post->ID ) );
			} else {
				update_post_meta( $post->ID, Restrict_Image_Uses::META_IMAGE_RESTRICTED_TYPE, 'single_use' );
				$changed = true;
			}

		} else {
			$this->_write_log( sprintf( 'Already post meta added: %d', $post->ID ) );
		}

		if ( ! $this->dry_run ) {

			if ( $changed ) {
				$this->_log_csv( $post->ID, $post->post_type, 'marked' );
				$this->_write_log( sprintf( 'Marked: %d, %s', $post->ID, $post->post_type ) );
			} else {
				$this->_log_csv( $post->ID, $post->post_type, 'exists' );
				$this->_write_log( sprintf( 'Exists: %d, %s', $post->ID, $post->post_type ) );
			}

		}

		// passive update the iteration counter to throttle the process and calling stop_the_insanity
		$this->_update_iteration();
	}

	/**
	 * @param int $id
	 * @param string $type
	 * @param string $status
	 */
	private function _log_csv( int $id, string $type, string $status = 'marked' ) : void {
		if ( ! empty( $this->_csv_stream ) ) {
			$row = [ $id, $status, $type ];
			fputcsv( $this->_csv_stream, $row );  // phpcs:ignore
		}

	}

}
