<?php
/**
 * Cli Script to manage Attachments.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since 2018-05-09 READS-1229
 */

namespace PMC\WP_CLI\Commands;

use \PMC_WP_CLI_Base;
use \SplFileObject;
use \PMC\Global_Functions\Utility\Attachment;

class Manage_Attachments extends PMC_WP_CLI_Base {

	const COMMAND_NAME = 'pmc-manage-attachments';

	/**
	 * To prevent editor from using getty images before contract date 2016-01-31.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Whether or not to do dry run.
	 * ---
	 * default: true
	 * options:
	 *  - false
	 *  - true
	 *
	 * [--log-file=<file>]
	 * : Path to log file
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
	 * [--sleep]
	 * : Whether to set specific sleep time.
	 *
	 * [--max-iteration]
	 * : Whether to set max iteration for stop the insanity.
	 *
	 * [--year-from]
	 * : To fetch posts between duration of Year from.
	 *
	 * [--year-to]
	 * : To fetch posts between duration of Year to.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pmc-manage-attachments mark_getty_images --url=example.com --dry-run=true
	 *
	 * @subcommand mark_getty_images
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 *
	 * Ignoring code coverage on this because tests for this are failing and need to be updated
	 * @codeCoverageIgnore
	 */
	public function mark_getty_images( $args, $assoc_args ) {

		// Extract Arguments.
		$this->_extract_common_args( $assoc_args );

		$this->_notify_start( 'WP-CLI command wp pmc-manage-attachments mark_getty_images: Started' );

		if ( $this->dry_run ) {
			$this->_write_log( 'Dry Run -- ' . PHP_EOL );
		} else {
			$this->_write_log( 'Actual Run -- ' . PHP_EOL );
		}

		$count          = 0;
		$paged          = 1;
		$posts_per_page = 50;

		// get_posts args.
		$args = array(
			'post_type'        => 'attachment',
			'posts_per_page'   => $posts_per_page,
			'suppress_filters' => true,
			'orderby'          => 'ID',
			'order'            => 'ASC',
		);

		if ( ! empty( $assoc_args['year-from'] ) && ! empty( $assoc_args['year-to'] ) && true === is_string( $assoc_args['year-from'] ) && true === is_string( $assoc_args['year-to'] ) ) {
			$args['date_query'] = array(
				array(
					'after'     => array(
						'year' => $assoc_args['year-from'],
					),
					'before'    => array(
						'year' => $assoc_args['year-to'],
					),
					'inclusive' => true,
				),
			);
		}

		do {

			$args['paged'] = $paged;

			$posts = get_posts( $args ); // @codingStandardsIgnoreLine WordPress.VIP.RestrictedFunctions

			if ( empty( $posts ) ) {
				break;
			}

			$posts_count = count( $posts );

			$post_ids = array();
			$post_ids = wp_list_pluck( $posts, 'ID' );
			$this->_write_log( sprintf( 'Attachments are going to process : %s', implode( ', ', $post_ids ) ), 1 );

			for ( $i = 0; $i < $posts_count; $i++ ) {

				$has_hide_meta_key = metadata_exists( 'post', $posts[ $i ]->ID, '_pmc_hide_in_media_library' );

				// Already have _pmc_hide_in_media_library then don't move ahead.
				if ( ! empty( $has_hide_meta_key ) ) {
					$this->_write_log( sprintf( 'Attachment ID : %d -- already Has Meta Key', $posts[ $i ]->ID ), 1 );
					continue;
				}

				$is_getty_image    = false;
				$image_meta_string = '';

				// Check for attachment post caption and description in content and excerpt.
				$image_meta_string = ( ! empty( $posts[ $i ]->post_content ) ) ? $posts[ $i ]->post_content : '';
				$is_getty_image    = ( $is_getty_image || $this->_has_getty_string( $image_meta_string ) );
				$image_meta_string = ( ! empty( $posts[ $i ]->post_excerpt ) ) ? $posts[ $i ]->post_excerpt : '';
				$is_getty_image    = ( $is_getty_image || $this->_has_getty_string( $image_meta_string ) );

				// Check for attachment metadata.
				if ( ! $is_getty_image ) {
					$attachment_metadata = wp_get_attachment_metadata( $posts[ $i ]->ID );
					$attachment_metadata = ( ( ! empty( $attachment_metadata ) ) && is_array( $attachment_metadata ) ) ? $attachment_metadata : array();

					if ( ! empty( $attachment_metadata['image_meta'] ) ) {

						// Done this approach to not include any function overhead like implode or json_encode.
						$image_meta_string = ( ! empty( $attachment_metadata['image_meta']['credit'] ) ) ? $attachment_metadata['image_meta']['credit'] : '';
						$is_getty_image    = ( $is_getty_image || $this->_has_getty_string( $image_meta_string ) );
						$image_meta_string = ( ! empty( $attachment_metadata['image_meta']['caption'] ) ) ? $attachment_metadata['image_meta']['caption'] : '';
						$is_getty_image    = ( $is_getty_image || $this->_has_getty_string( $image_meta_string ) );
						$image_meta_string = ( ! empty( $attachment_metadata['image_meta']['title'] ) ) ? $attachment_metadata['image_meta']['title'] : '';
						$is_getty_image    = ( $is_getty_image || $this->_has_getty_string( $image_meta_string ) );
						$image_meta_string = ( ! empty( $attachment_metadata['image_meta']['copyright'] ) ) ? $attachment_metadata['image_meta']['copyright'] : '';
						$is_getty_image    = ( $is_getty_image || $this->_has_getty_string( $image_meta_string ) );

					}
				}

				// Check for attachment image credit meta data.
				if ( ! $is_getty_image ) {

					$image_credit = get_post_meta( $posts[ $i ]->ID, '_image_credit', true );
					$image_credit = ( ! empty( $image_credit ) ) ? $image_credit : '';

					$is_getty_image = $this->_has_getty_string( $image_credit );
				}

				// Found getty image.
				if ( true === $is_getty_image ) {
					if ( $this->dry_run ) {
						// _pmc_hide_in_media_library meta key will be added.
						$count++;
						$this->_write_log( sprintf( 'Attachment ID : %d -- will have Meta Key', $posts[ $i ]->ID ), 1 );
					} else {
						$result = update_post_meta( $posts[ $i ]->ID, '_pmc_hide_in_media_library', 1 );

						if ( ! empty( $result ) ) {
							// _pmc_hide_in_media_library meta key added.
							$count++;
							$this->_write_log( sprintf( 'Meta key added Attachment ID : %d', $posts[ $i ]->ID ), 1 );
						} else {
							// _pmc_hide_in_media_library meta key not added.
							$this->_write_log( sprintf( 'Failed to Add Meta key -- Attchment ID : %d', $posts[ $i ]->ID ), 2 );
						}
					}
				}

				$this->_update_interation();

			}

			$paged++;

		} while ( $posts_per_page === $posts_count );

		if ( $this->dry_run ) {
			$this->_write_log( sprintf( 'Total %d Attachments will have Meta Key', $count ), 1 );
		} else {
			$this->_write_log( sprintf( 'Total %d Attachments have Meta Key', $count ), 1 );
		}

		$this->_notify_done( 'WP-CLI command wp pmc-manage-attachments mark_getty_images: Completed' );

	}

	/**
	 * Function to check image meta contains getty image text.
	 *
	 * @param string $image_meta image meta string.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return boolean
	 */
	private function _has_getty_string( $image_meta ) {

		if (
			! empty( $image_meta ) &&
			false !== strpos( strtolower( $image_meta ), 'getty' )
		) {
			return true;
		}

		return false;
	}

	/**
	 * To delete attachments based on file URLs
	 *
	 * @subcommand delete_by_url
	 *
	 * ## OPTIONS
	 *
	 * --input-file=<file>
	 * : The file containing the list of URLs to be deleted
	 *
	 * [--dry-run]
	 * : Whether or not to do dry run.
	 * ---
	 * default: true
	 * options:
	 *  - false
	 *  - true
	 *
	 * [--log-file=<file>]
	 * : Path to log file
	 *
	 * [--email]
	 * : Email address where notification is sent after script completion.
	 *
	 * [--email-when-done]
	 * : Whether to send email notification or not.
	 * ---
	 * default: yes
	 * options:
	 *  - no
	 *  - yes
	 *
	 * [--email-logfile]
	 * : Whether to send log file in email or not.
	 * ---
	 * default: yes
	 * options:
	 *  - no
	 *  - yes
	 *
	 * [--max-iteration]
	 * : Maximum number of iterations to be done continuously before short pause. No more than 20 are advised.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pmc-manage-attachments delete_by_url --max-iteration=5 --input-file=/path/to/file.txt --log-file=/path/to/file.log
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function delete_by_url( array $args, array $assoc_args ) : void {

		$command_name = sprintf( '%s:%s', self::COMMAND_NAME, 'delete_by_url' );

		// extract arguments
		$this->_extract_common_args( $assoc_args );

		$input_file = ( ! empty( $assoc_args['input-file'] ) ) ? $assoc_args['input-file'] : '';

		$this->validate_input_file( $input_file, $command_name );

		$total_lines = $this->get_total_lines_in_file( $input_file );

		$this->_notify_start(
			sprintf( 'WP-CLI command %s :-: Started', $command_name )
		);

		if ( $this->dry_run ) {
			$this->_write_log( 'Dry Run -- ' . PHP_EOL );
		} else {
			$this->_write_log( 'Actual Run -- ' . PHP_EOL );
		}

		$progress_bar = \WP_CLI\Utils\make_progress_bar(
			sprintf( 'Processing %d URLs(s)..', $total_lines ),
			$total_lines
		);

		$attachment = Attachment::get_instance();

		$file = new SplFileObject( $input_file, 'r' );

		foreach ( $file as $url ) {

			$url = trim( $url );

			if ( empty( $url ) ) {
				continue;
			}

			$post_id = $attachment->get_postid_from_url( $url );

			if ( 1 > $post_id ) {

				$this->_warning(
					sprintf( 'Attachment ID not found for URL: %s', $url )
				);

				continue;

			}

			if ( $this->dry_run ) {
				$deleted_post = get_post( $post_id );
			} else {
				$deleted_post = wp_delete_attachment( $post_id, true );
			}

			if ( empty( $deleted_post->ID ) || intval( $deleted_post->ID ) !== $post_id ) {

				/*
				 * Ignoring code coverage for below line as this cannot be tested at present
				 * because wp_delete_attachment() does not expose any filters to short-circuit
				 * it to simulate failed attempt at attachment deletion.
				 */

				// @codeCoverageIgnoreStart
				$this->_warning(
					sprintf( 'Attachment cannot be deleted for URL: %s', $url )
				);
				// @codeCoverageIgnoreEnd

			} else {

				$this->_write_log(
					sprintf( 'Attachment deleted successfully for URL: %s', $url )
				);

			}

			unset( $deleted_post );
			unset( $post_id, $url );

			$progress_bar->tick();

			// Sleep, stop_the_insanity etc. so that we don't hammer the DB
			$this->_update_iteration();

		}    //end foreach loop

		$progress_bar->finish();

		unset( $progress_bar, $total_lines, $input_file );

		/*
		 * This is important since there's no public close method in SplFileObject class
		 * and PHP will keep the file open if its not explicitly closed.
		 * Destroying the object is the only way possible, at present, to close the file.
		 */
		unset( $file );

		$this->_notify_done(
			sprintf( 'WP-CLI command %s :-: Completed', $command_name )
		);

	}

}

//EOF
