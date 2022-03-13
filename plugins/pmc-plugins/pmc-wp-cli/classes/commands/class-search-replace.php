<?php
/**
 * WP CLI command to perform search/replace operation in post content fields
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-07-02
 */

namespace PMC\WP_CLI\Commands;

use \PMC_WP_CLI_Base;
use \SplFileObject;
use \WP_Post;
use \PMC\Global_Functions\Utility\Attachment;

class Search_Replace extends PMC_WP_CLI_Base {

	const COMMAND_NAME = 'pmc-search-replace';

	/**
	 * Method to read post IDs from an input file and return them as an array
	 *
	 * @param string $file_path
	 * @param string $command_name
	 *
	 * @return array
	 */
	protected function _get_post_ids_in_array( string $file_path, string $command_name = '' ) : array {

		$post_ids = [];

		if ( empty( $file_path ) ) {
			return $post_ids;
		}

		$this->validate_input_file( $file_path, $command_name );

		$file = new SplFileObject( $file_path, 'r' );

		foreach ( $file as $id ) {
			$post_ids[] = intval( $id );
		}

		/*
		 * This is important since there's no public close method in SplFileObject class
		 * and PHP will keep the file open if its not explicitly closed.
		 * Destroying the object is the only way possible, at present, to close the file.
		 */
		unset( $file );

		$post_ids = array_filter(
			array_unique( (array) $post_ids )
		);

		sort( $post_ids );

		return $post_ids;

	}

	/**
	 * Method to read a CSV file containing items to search and their replacements and return the data as array
	 *
	 * @param string $file_path
	 * @param string $command_name
	 *
	 * @return array
	 */
	protected function _get_search_replace_tokens_in_array( string $file_path, string $command_name = '' ) : array {

		$data = [];

		if ( empty( $file_path ) ) {
			return $data;
		}

		$this->validate_input_file( $file_path, $command_name );

		$file = new SplFileObject( $file_path, 'r' );
		$file->setFlags( SplFileObject::READ_CSV );

		foreach ( $file as $row ) {

			if ( empty( $row ) || ! is_array( $row ) || count( $row ) !== 2 ) {
				continue;
			}

			list( $search_for, $replace_with ) = $row;

			if ( empty( $search_for ) ) {
				continue;
			}

			$data[] = [
				's' => $search_for,
				'r' => $replace_with,
			];

		}

		/*
		 * This is important since there's no public close method in SplFileObject class
		 * and PHP will keep the file open if its not explicitly closed.
		 * Destroying the object is the only way possible, at present, to close the file.
		 */
		unset( $file );

		return $data;

	}

	/**
	 * Method to read a CSV file containing items to search and their replacements and return the data as array
	 *
	 * @param string $file_path
	 * @param string $command_name
	 *
	 * @return array
	 */
	protected function _get_post_ids_search_replace_tokens_in_array( string $file_path, string $command_name = '' ) : array {

		$data = [];

		if ( empty( $file_path ) ) {
			return $data;
		}

		$this->validate_input_file( $file_path, $command_name );

		$file = new SplFileObject( $file_path, 'r' );
		$file->setFlags( SplFileObject::READ_CSV );

		foreach ( $file as $row ) {
			if ( empty( $row ) || ! is_array( $row ) || count( $row ) !== 3 ) {
				continue;
			}

			list( $gallery_id, $search_for, $replace_with ) = $row;

			if ( empty( $search_for ) ) {
				continue;
			}

			$data[] = [
				'i' => $gallery_id,
				's' => $search_for,
				'r' => $replace_with,
			];

		}

		/*
		 * This is important since there's no public close method in SplFileObject class
		 * and PHP will keep the file open if its not explicitly closed.
		 * Destroying the object is the only way possible, at present, to close the file.
		 */
		unset( $file );

		return $data;

	}

	/**
	 * Method to do image replacement in post of 'pmc-attachments' type
	 *
	 * @param \WP_Post $post
	 * @param string   $search_for
	 * @param string   $replace_with
	 *
	 * @return \WP_Post
	 */
	protected function _get_search_replaced_pmc_attachment( WP_Post $post, string $search_for, string $replace_with ) : WP_Post {

		if ( empty( $search_for ) || empty( $replace_with ) ) {
			return $post;
		}

		$attachment = Attachment::get_instance();

		$old_attachment_id = $attachment->get_postid_from_url( $search_for );
		$new_attachment_id = $attachment->get_postid_from_url( $replace_with );

		if ( 1 > $old_attachment_id || 1 > $new_attachment_id ) {
			return $post;
		}

		$post->post_parent = ( intval( $post->post_parent ) === $old_attachment_id ) ? $new_attachment_id : $post->post_parent;
		$post->post_title  = str_replace( $old_attachment_id, $new_attachment_id, $post->post_title );
		$post->post_name   = str_replace( $old_attachment_id, $new_attachment_id, $post->post_name );
		$post->guid        = str_replace( $old_attachment_id, $new_attachment_id, $post->guid );

		unset( $new_attachment_id, $old_attachment_id );

		return $post;

	}

	/**
	 * Method to do image replacement in post of 'pmc-gallery' type
	 *
	 * @param \WP_Post $post
	 * @param string   $search_for
	 * @param string   $replace_with
	 *
	 * @return \WP_Post
	 */
	protected function _get_search_replaced_pmc_gallery( WP_Post $post, string $search_for, string $replace_with ) : WP_Post {

		if ( empty( $search_for ) || empty( $replace_with ) ) {
			return $post;
		}

		$attachment = Attachment::get_instance();

		$old_attachment_id = $attachment->get_postid_from_url( $search_for );
		$new_attachment_id = $attachment->get_postid_from_url( $replace_with );

		if ( 1 > $old_attachment_id || 1 > $new_attachment_id ) {
			return $post;
		}

		$pmc_attachments_type       = 'pmc-attachments';
		$does_pmc_attachments_exist = post_type_exists( $pmc_attachments_type );

		$meta_key   = 'pmc-gallery';
		$meta_value = get_post_meta( $post->ID, $meta_key, true );

		if ( empty( $meta_value ) ) {

			$this->_warning(
				sprintf( 'Image attachments not found in Gallery ID %d', $post->ID ) . PHP_EOL
			);

			return $post;

		}

		$key = array_search( $old_attachment_id, (array) $meta_value, true );

		// Updated to allow a key with index of zero for arrays that aren't keyed with `pmc-attachments` ID.
		if ( empty( $key ) && ( 0 !== $key ) ) {

			$this->_write_log(
				sprintf( 'Gallery ID %d does not need to be updated', $post->ID )
			);

			return $post;

		}

		$key = intval( $key );

		$meta_value[ $key ] = $new_attachment_id;

		if ( true === $does_pmc_attachments_exist && get_post_type( $key ) === $pmc_attachments_type ) {

			$pmc_attachments_post = get_post( $key );

			$pmc_attachments_post->post_content = str_replace( $search_for, $replace_with, $pmc_attachments_post->post_content );

			$pmc_attachments_post = $this->_get_search_replaced_pmc_attachment( $pmc_attachments_post, $search_for, $replace_with );

			if ( $this->dry_run ) {
				$updated_post_id = $key;
			} else {
				$updated_post_id = wp_update_post( $pmc_attachments_post, true );
			}

			if (
				empty( $updated_post_id ) || is_wp_error( $updated_post_id )
				|| intval( $updated_post_id ) !== $key
			) {

				$this->_warning(
					sprintf( 'PMC Attachment ID %d cannot be updated for Gallery ID %d', $key, $post->ID ) . PHP_EOL
				);

			} else {

				$this->_write_log(
					sprintf( 'PMC Attachment ID %d updated successfully for Gallery ID %d', $key, $post->ID )
				);

			}

			unset( $updated_post_id, $pmc_attachments_post );

		}    // end pmc-attachment update block

		if ( ! $this->dry_run ) {
			update_post_meta( $post->ID, $meta_key, $meta_value );
		}

		$this->_write_log(
			sprintf( 'Gallery ID %d meta data successfully updated', $post->ID )
		);

		unset( $key, $meta_value, $meta_key, $does_pmc_attachments_exist, $pmc_attachments_type );
		unset( $new_attachment_id, $old_attachment_id );

		return $post;

	}

	/**
	 * To search and replace text in content of specific posts
	 *
	 * @subcommand post_content_text
	 *
	 * ## OPTIONS
	 *
	 * --post-ids-file=<file>
	 * : The file containing post IDs in which search-replace is to be done
	 *
	 * --csv=<file>
	 * : The file containing the list of strings to be replaced in posts
	 *
	 * [--is-image-replacement]
	 * : Whether the replacement is for image URLs or not
	 * ---
	 * default: true
	 * options:
	 *  - false
	 *  - true
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
	 *     wp pmc-search-replace post_content_text --max-iteration=5 --csv=/path/to/file.csv --log-file=/path/to/file.log
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function post_content_text( array $args, array $assoc_args ) : void {

		$command_name = sprintf( '%s:%s', self::COMMAND_NAME, 'post_content_text' );

		// extract arguments
		$this->_extract_common_args( $assoc_args );

		$post_ids_file = ( ! empty( $assoc_args['post-ids-file'] ) ) ? $assoc_args['post-ids-file'] : '';
		$csv_file      = ( ! empty( $assoc_args['csv'] ) ) ? $assoc_args['csv'] : '';

		$is_image_replacement = false;

		if ( isset( $assoc_args['is-image-replacement'] ) ) {

			$is_image_replacement = (
				( empty( $assoc_args['is-image-replacement'] ) && false !== $assoc_args['is-image-replacement'] )
				|| in_array( $assoc_args['is-image-replacement'], [ 1, 'yes', 'true', true ], true )
			);

		}

		$post_ids       = $this->_get_post_ids_in_array( $post_ids_file, $command_name );
		$search_replace = $this->_get_search_replace_tokens_in_array( $csv_file, $command_name );

		if ( empty( $post_ids ) ) {

			$this->_error( 'Post IDs data file is empty.' );

			// for some reason the error method above is not exiting script in unit tests
			return;    // @codeCoverageIgnore

		}

		if ( empty( $search_replace ) ) {

			$this->_error( 'Search/Replace data file is empty.' );

			// for some reason the error method above is not exiting script in unit tests
			return;    // @codeCoverageIgnore

		}

		$post_ids_count       = count( $post_ids );
		$search_replace_count = count( $search_replace );
		$attachment           = Attachment::get_instance();

		$this->_notify_start(
			sprintf( 'WP-CLI command %s :-: Started', $command_name )
		);

		if ( $this->dry_run ) {
			$this->_write_log( 'Dry Run -- ' . PHP_EOL );
		} else {
			$this->_write_log( 'Actual Run -- ' . PHP_EOL );
		}

		$progress_bar = \WP_CLI\Utils\make_progress_bar(
			sprintf( 'Processing %d post(s)..', $post_ids_count ),
			$post_ids_count
		);

		/*
		 * Loop over post IDs
		 */
		for ( $i = 0; $i < $post_ids_count; $i++ ) {

			$post_id = intval( $post_ids[ $i ] );

			$this->_write_log(
				sprintf( 'Starting on Post ID %d', $post_id )
			);

			$this->_write_log( '--------------------' );

			$post = get_post( $post_id );

			if ( empty( $post ) ) {

				$this->_warning(
					sprintf( 'Post ID %d not found', $post_id ) . PHP_EOL
				);

				continue;

			}

			/*
			 * Loop to do search/replace
			 */
			for ( $j = 0; $j < $search_replace_count; $j++ ) {

				$search_for   = $search_replace[ $j ]['s'];
				$replace_with = $search_replace[ $j ]['r'];

				$post->post_content = str_replace( $search_for, $replace_with, $post->post_content );

				if ( true !== $is_image_replacement ) {
					continue;
				}

				$old_attachment_id = $attachment->get_postid_from_url( $search_for );
				$new_attachment_id = $attachment->get_postid_from_url( $replace_with );

				if ( 1 > $old_attachment_id || 1 > $new_attachment_id ) {

					$msg  = 'One or both of following URLs is not an attachment' . PHP_EOL;
					$msg .= 'S = %s' . PHP_EOL;
					$msg .= 'R = %s' . PHP_EOL;

					$this->_warning(
						sprintf( $msg, $search_for, $replace_with )
					);

					continue;

				}

				$post_type = get_post_type( $post );

				switch ( $post_type ) {

					case 'pmc-attachments':
						// update additional stuff here
						$post = $this->_get_search_replaced_pmc_attachment( $post, $search_for, $replace_with );
						break;

					case 'pmc-gallery':
						// update additional stuff here
						$post = $this->_get_search_replaced_pmc_gallery( $post, $search_for, $replace_with );
						break;

					case 'attachment':
						// nothing more to update
						break;

					case 'post':
					case 'page':
					default:
						// update additional stuff here
						$meta_fields_to_update = [ '_thumbnail_id' ];
						$meta_fields_to_update = apply_filters(
							'pmc_wp_cli_search_replace_post_content_image_meta_fields',
							$meta_fields_to_update,
							$post,
							$search_for,
							$replace_with
						);

						if ( ! empty( $meta_fields_to_update ) && is_array( $meta_fields_to_update ) ) {

							$meta_fields_to_update = array_filter(
								array_unique( (array) $meta_fields_to_update )
							);

							sort( $meta_fields_to_update );

							foreach ( $meta_fields_to_update as $meta_field ) {

								$meta_value = '';

								if ( metadata_exists( 'post', $post->ID, $meta_field ) ) {
									$meta_value = get_post_meta( $post->ID, $meta_field, true );
								}

								if (
									! $this->dry_run && ! empty( $meta_value )
									&& ( is_string( $meta_value ) || is_array( $meta_value ) )
								) {

									$meta_value = str_replace( $old_attachment_id, $new_attachment_id, $meta_value );
									update_post_meta( $post->ID, $meta_field, $meta_value );

								}

								unset( $meta_value );

							}

							$this->_write_log(
								sprintf( 'Image meta data updated for Post ID %d', $post_id )
							);

						}

						unset( $meta_fields_to_update );

						break;

				}

				unset( $new_attachment_id, $old_attachment_id );
				unset( $post_type, $replace_with, $search_for );

				/*
				 * Let's stop for a second on every 2nd iteration of search-replace
				 * so that we don't tax the DB and this command runs smoothly
				 * when there are a lot of search-replace tokens.
				 */
				if ( 0 === ( $j % 2 ) ) {

					sleep( 1 );
					$this->stop_the_insanity();

				}

			}    // end search-replace loop

			if ( $this->dry_run ) {
				$updated_post_id = $post_id;
			} else {
				$updated_post_id = wp_update_post( $post, true );
			}

			$this->_write_log( '--------------------' );

			if (
				empty( $updated_post_id ) || is_wp_error( $updated_post_id )
				|| intval( $updated_post_id ) !== $post_id
			) {

				$this->_warning(
					sprintf( 'Post ID %d cannot be updated', $post_id ) . PHP_EOL
				);

			} else {

				$this->_write_log(
					sprintf( 'Post ID %d updated successfully', $post_id )
				);

			}

			unset( $post );
			unset( $post_id, $search_for, $replace_with );

			$progress_bar->tick();

			// Sleep, stop_the_insanity etc. so that we don't hammer the DB
			$this->_update_iteration();

		}    //end post IDs loop

		$progress_bar->finish();

		unset( $search_replace_count, $post_ids_count, $search_replace, $post_ids, $is_image_replacement );
		unset( $progress_bar, $csv_file, $post_ids_file );

		$this->_notify_done(
			sprintf( 'WP-CLI command %s :-: Completed', $command_name )
		);

	}

	/**
	 * To search and replace gallery images.
	 *
	 * @subcommand post_gallery_content_text
	 *
	 * ## OPTIONS
	 *
	 *
	 * --csv=<file>
	 * : The file containing the list of strings to be replaced in posts
	 *
	 * [--is-image-replacement]
	 * : Whether the replacement is for image URLs or not
	 * ---
	 * default: true
	 * options:
	 *  - false
	 *  - true
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
	 *     wp pmc-search-replace post_gallery_content_text --max-iteration=5 --csv=/path/to/file.csv --log-file=/path/to/file.log
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function post_gallery_content_text( array $args, array $assoc_args ) : void {

		$command_name = sprintf( '%s:%s', self::COMMAND_NAME, 'post_gallery_content_text' );

		// extract arguments
		$this->_extract_common_args( $assoc_args );

		$csv_file = ( ! empty( $assoc_args['csv'] ) ) ? $assoc_args['csv'] : '';

		$search_replace = $this->_get_post_ids_search_replace_tokens_in_array( $csv_file, $command_name );

		if ( empty( $search_replace ) ) {

			$this->_error( 'Search/Replace data file is empty.' );

			// for some reason the error method above is not exiting script in unit tests
			return;    // @codeCoverageIgnore

		}

		$search_replace_count = count( $search_replace );
		$attachment           = Attachment::get_instance();

		$this->_notify_start(
			sprintf( 'WP-CLI command %s :-: Started', $command_name )
		);

		if ( $this->dry_run ) {
			$this->_write_log( 'Dry Run -- ' . PHP_EOL );
		} else {
			$this->_write_log( 'Actual Run -- ' . PHP_EOL );
		}

		$progress_bar = \WP_CLI\Utils\make_progress_bar(
			sprintf( 'Processing %d image(s)..', $search_replace_count ),
			$search_replace_count
		);

		/*
		 * Loop over post IDs
		 */
		for ( $i = 0; $i < $search_replace_count; $i++ ) {

			$post_id = intval( $search_replace[ $i ]['i'] );

			$this->_write_log(
				sprintf( 'Starting on Post ID %d', $post_id )
			);

			$this->_write_log( '--------------------' );

			$post = get_post( $post_id );

			if ( ! is_a( $post, '\WP_Post' ) ) {

				$this->_warning(
					sprintf( 'Post ID %d not found', $post_id ) . PHP_EOL
				);

				continue;

			}

			if ( 'pmc-gallery' !== $post->post_type ) {

				$this->_warning(
					sprintf( 'Post %d not a Gallery', $post_id ) . PHP_EOL
				);

				continue;

			}

			$search_for   = $search_replace[ $i ]['s'];
			$replace_with = $search_replace[ $i ]['r'];

			$old_attachment_id = $attachment->get_postid_from_url( $search_for );
			$new_attachment_id = $attachment->get_postid_from_url( $replace_with );

			if ( 1 > $old_attachment_id || 1 > $new_attachment_id ) {

				$msg  = 'One or both of following URLs is not an attachment' . PHP_EOL;
				$msg .= 'S = %s' . PHP_EOL;
				$msg .= 'R = %s' . PHP_EOL;

				$this->_warning(
					sprintf( $msg, $search_for, $replace_with )
				);

				continue;

			}

			$post = $this->_get_search_replaced_pmc_gallery( $post, $search_for, $replace_with );

			unset( $new_attachment_id, $old_attachment_id );
			unset( $replace_with, $search_for );

			$this->_write_log( '--------------------' );

			unset( $post );
			unset( $post_id, $search_for, $replace_with );

			$progress_bar->tick();

			// Sleep, stop_the_insanity etc. so that we don't hammer the DB
			$this->_update_iteration();

		}    //end post IDs loop

		$progress_bar->finish();

		unset( $search_replace_count, $search_replace );
		unset( $progress_bar, $csv_file );

		$this->_notify_done(
			sprintf( 'WP-CLI command %s :-: Completed', $command_name )
		);

	}

}    //end class

//EOF
