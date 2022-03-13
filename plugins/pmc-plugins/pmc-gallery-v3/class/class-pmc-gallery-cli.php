<?php

if ( ! class_exists( 'WP_CLI_Command' ) ) {
	return false;
}

/**
 * Class is used to define WP_CLI commands.
 */
class PMC_Gallery_CLI extends WP_CLI_Command {

	/**
	 * Use to migrate data from old gallery data structure to new structure.
	 * For 'pmc-gallery' post type.
	 * To migrate gallery data from pmc-gallery-v2
	 * to pmc-gallery-v3 for uniqe metadata feture.
	 *
	 * ##OPTIONS
	 * [--dry-run]
	 * Return how many gallery is going to udpate along with gallery ids.
	 * wp pmc-gallery migrate --dry-run
	 *
	 * ## EXAMPLES
	 * wp pmc-gallery migrate
	 *
	 * @param array $args List of argument passed in cli.
	 * @param array $assoc_args List of Accosiative argument passed in cli.
	 */
	public function migrate( $args, $assoc_args ) {
		$is_dry_run = isset( $assoc_args['dry-run'] ) ? true : false;
		$page = 0;
		$response = array(
			'succeed'	=> array(),
			'failed'	=> array(),
		);
		/**
		 * Get instance of `PMC\Gallery\Attachment\Detail` class,
		 * To perfprm gallery's attchment's operations.
		 */
		$gallery_data = \PMC\Gallery\Attachment\Detail::get_instance();
		do {
			$page++;
			$query = new WP_Query(
				array(
					'post_type' => PMC_Gallery_Defaults::name,
					'paged' => $page,
				)
			);
			// Go throught each posts.
			foreach ( $query->posts as $post ) {
				// Get old data of gallery from meta field.
				$gallery_attachments = get_post_meta( $post->ID, PMC_Gallery_Defaults::name, true );
				$gallery_attachments = ( isset( $gallery_attachments ) && is_array( $gallery_attachments ) ) ? $gallery_attachments : array();
				$new_gallery_meta = array();
				// Go throught each attachment.
				foreach ( $gallery_attachments as $maybe_variant_id => $attachment_id ) {
					$variant_id = false;
					$attachment_id = absint( $attachment_id );

					// Get attachment data to set in private post of gallery attachment.
					$attachment = get_post( $attachment_id );
					$data = array();
					if ( ! $attachment ) {
						continue;
					}
					if ( 'attachment' !== $attachment->post_type ) {
						continue;
					}
					/**
					 * Check if value in key is post id of private attachment.
					 * If yes then we won't create whole new post for it,
					 * instead of update it self.
					 */
					if ( get_post_type( $maybe_variant_id ) === \PMC\Gallery\Attachment\Detail::name ) {
						$variant_id = intval( $maybe_variant_id );
					}
					// Get attachment meta data at once for `alt` and `image_credit`.
					$attachment_meta_data = get_post_meta( $attachment_id );
					$data = array(
						'id' => $attachment->ID,
						'title' => $attachment->post_title,
						'description' => $attachment->post_content,
						'caption' => $attachment->post_excerpt,
						'alt' => ! empty( $attachment_meta_data['_wp_attachment_image_alt'][0] ) ? $attachment_meta_data['_wp_attachment_image_alt'][0] : '',
						'image_credit' => ! empty( $attachment_meta_data['_image_credit'][0] ) ? $attachment_meta_data['_image_credit'][0] : '',
					);
					if ( $is_dry_run ) {
						$variant_id = count( $new_gallery_meta ) + 1;
					} else {
						$variant_id = $gallery_data->add_attachment_variant( $post->ID, $data, $variant_id );
					}
					$variant_id = absint( $variant_id );
					if ( $variant_id && is_numeric( $variant_id ) ) {
						/**
						 * Create new meta for gallery,
						 * which have `variant_id` as key and `attachment_id` as value.
						 * i.g. array( variant_id => attachment_id );
						 */
						$new_gallery_meta[ $variant_id ] = $attachment_id;
					}
				}

				if ( $is_dry_run ) {
					if ( count( $new_gallery_meta ) ) {
						$flag = true;
					} else {
						$flag = false;
					}
				} else {
					if ( count( $new_gallery_meta ) ) {
						// If new meta data is not empty than update meta field.
						$flag = true;
						update_post_meta( $post->ID, PMC_Gallery_Defaults::name, $new_gallery_meta );
					} else {
						// If meta data is empty than delete meta field.
						$flag = false;
						delete_post_meta( $post->ID, PMC_Gallery_Defaults::name );
					}
				}
				if ( $flag ) {
					$response['succeed'][] = $post->ID;
				} else {
					$response['failed'][] = $post->ID;
				}
			}
		} while ( $page < $query->max_num_pages ); // Loop until all gallery is not conver.

		$succeed_count = count( $response['succeed'] );
		$faild_count = count( $response['failed'] );
		if ( $is_dry_run ) {
			if ( $succeed_count ) {
				WP_CLI::success( sprintf( __( 'Migration will perform on %d galleries : ', 'pmc-gallery' ), $succeed_count ) );
				WP_CLI::success( __( 'Migration will perform on following galleries : ', 'pmc-gallery' ) . implode( ', ', $response['succeed'] ) );
			}
			if ( $faild_count ) {
				WP_CLI::error( sprintf( __( 'On %d gallery migration won\'t be perform : ', 'pmc-gallery' ) , $faild_count ) , false );
				WP_CLI::error( __( 'Migration won\'t perform on following galleries : ', 'pmc-gallery' ) . implode( ', ', $response['failed'] ) );
			}
		} else {
			if ( $succeed_count ) {
				WP_CLI::success( __( 'Galleries migrated : ', 'pmc-gallery' ) . $succeed_count );
				WP_CLI::success( __( 'Following Galleries migrated : ', 'pmc-gallery' ) . implode( ', ', $response['succeed'] ) );
			}
			if ( $faild_count ) {
				WP_CLI::error( __( 'Failed to migrate : ', 'pmc-gallery' ) . $faild_count, false );
				WP_CLI::error( __( 'Following Galleries are failed to migrate  : ', 'pmc-gallery' ) . implode( ', ', $response['failed'] ) );
			}
		}
	}
}

WP_CLI::add_command( 'pmc-gallery', 'PMC_Gallery_CLI' );
