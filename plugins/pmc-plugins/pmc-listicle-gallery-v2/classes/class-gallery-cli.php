<?php

namespace PMC\Listicle_Gallery_V2;

use PMC;
use PMC\Listicle_Gallery_V2\Services\Gallery;
use PMC\Listicle_Gallery_V2\Services\Gallery_Item;
use PMC\Listicle_Slideshow\Listicle_Slideshow;


/**
 * WP-Cli command for converting listicle slideshow posts to the new listicle gallery post type.
 *
 * Terminology:
 *
 *  old content:
 *    slideshow           - this is a custom post type
 *    slideshow gallery   - this is defined by meta data attached to a slideshow
 *
 *  new content:
 *    gallery             - this is a custom post type, with links to a collection of "gallery item" posts
 *    gallery item        - this is a custom post type
 *
 *
 * Commands:
 *
 *   convert  - starts the conversion process
 *   rollback - rolls back the conversion
 *
 *
 * Conversion process:
 *
 *  Data from slideshow galleries will be used to create new gallery items.
 *  Slideshow post types will be changed to the new listicle gallery post type.
 *  The newly created gallery items will be attached to their corresponding slideshows as meta data.
 *
 *
 * Rollback process:
 *
 * The gallery items meta data will be deleted from each gallery.
 * Each gallery's post type will be reset to the original pre-conversion slideshow post type.
 *
 */

class Gallery_CLI extends \PMC_WP_CLI_Base {

	const BATCH_SIZE = 100;

	/**
	 * The main conversion loop.
	 *
	 * Retrieves all listicle slideshow posts and uses their data to
	 * create new listicle gallery and listicle gallery item posts.
	 *
	 * @param $args
	 * @param array $assoc_args
	 */
	public function convert( $args, $assoc_args = [ ] ) {

		$this->_extract_common_args( $assoc_args );
		\WP_CLI::confirm( sprintf( 'Ok to proceed with the %s conversion?', ( $this->dry_run ) ? 'DRY RUN' : 'LIVE' ), $assoc_args );

		$count_posts = wp_count_posts( Listicle_Slideshow::POST_TYPE );
		$total_posts = ( isset( $count_posts->publish ) ) ? intval( $count_posts->publish ) : 0;
		$total_posts += ( isset( $count_posts->draft ) ) ? intval( $count_posts->draft ) : 0;
		$total_posts += ( isset( $count_posts->future ) ) ? intval( $count_posts->future ) : 0;
		$total_posts += ( isset( $count_posts->pending ) ) ? intval( $count_posts->pending ) : 0;
		$total_posts += ( isset( $count_posts->private ) ) ? intval( $count_posts->private ) : 0;
		$this->_write_log( sprintf( 'Total number of slideshows to convert: %d', $total_posts ) );

		$offset = 0;
		$slideshows = $this->_get_slideshows( $offset );

		while ( ! empty( $slideshows ) ) {

			// convert the current batch of slideshows

			$this->_convert_slideshows( $slideshows );

			// get the next batch

			$this->stop_the_insanity();
			sleep( 1 );
			$offset += self::BATCH_SIZE;
			$slideshows = $this->_get_slideshows( $offset );

		}

		if ( ! $this->dry_run ) {
			pmc_update_option( PMC_SLIDESHOWS_CONVERTED, 'yes' );
		}

		$this->_success( 'The conversion process is finished' );

	}

	/**
	 * Returns a batch of slideshow posts
	 *
	 * Note: there is no need to use WP_Query's 'offset' parameter to skip over
	 * records because we're changing the post type when each batch is converted
	 * and so the converted items will automatically be excluded from the query.
	 *
	 * @param int $offset
	 * @return array
	 */
	protected function _get_slideshows( $offset ) {

		$this->_write_log( sprintf( 'Getting batch %d of slideshows...', ( $offset / self::BATCH_SIZE  ) ) );

		// Don't use offset in live mode:
		// we're changing the post type as each batch is processed and since post type is one of the query's
		// selection criteria, the converted items will be automatically excluded from the results.

		if ( ! $this->dry_run ) {
			$offset = 0;
		}

		$query = new \WP_Query( [
			'post_type'        => Listicle_Slideshow::POST_TYPE,
			'posts_per_page'   => self::BATCH_SIZE,
			'offset'           => $offset,
		] );

		if ( is_wp_error( $query ) ) {
			$this->_error( $query->get_error_message() );
		}

		$this->_write_log( sprintf( 'Found %d slideshows', count( $query->posts ) ) );

		return $query->posts;

	}

	/**
	 * Converts the given slideshows
	 *
	 * @param array $slideshows  The slideshow posts to convert
	 */
	protected function _convert_slideshows( $slideshows ) {

		$this->_write_log( 'Converting slideshows...' );

		if ( empty( $slideshows ) ) {
			$this->_write_log( 'Did not find any slideshows to convert' );
			return;
		}

		$count = 0;

		foreach ( $slideshows as $slideshow ) {

			$gallery_item_ids = [ ];

			$slideshow_galleries = $this->_get_slideshow_galleries( $slideshow->ID );

			if ( ! empty( $slideshow_galleries ) && is_array( $slideshow_galleries ) ) {
				foreach ( $slideshow_galleries as $slideshow_gallery ) {
					$this->_update_images( $slideshow_gallery );
					$gallery_item_ids[] = $this->_create_gallery_item( $slideshow_gallery, $slideshow->ID );
				}
			}

			$this->_convert_slideshow( $slideshow->ID, $gallery_item_ids );
			$count ++;

		}

		$this->_write_log( sprintf( 'Converted %d slideshows to listicle galleries', $count ) );

	}

	/**
	 * Returns the 'galleries' meta data for the given slideshow
	 *
	 * @param int $slideshow_id
	 * @return array
	 */
	protected function _get_slideshow_galleries( $slideshow_id ) {

		$this->_write_log( sprintf( 'Getting the galleries for slideshow %d...', $slideshow_id ) );

		$slideshow_galleries = get_post_meta( $slideshow_id, 'galleries', false );
		$count = 0;

		if ( ! empty( $slideshow_galleries ) && is_array( $slideshow_galleries ) ) {
			$count = count( $slideshow_galleries[ 0 ] );
		}

		$this->_write_log( sprintf( 'Found %d galleries for slideshow %d', $count, $slideshow_id ) );

		return $slideshow_galleries[ 0 ];
	}

	/**
	 * Retrieves the images attached to the slideshow gallery and updates blank title, alt, caption,
	 * and credit fields using the corresponding fields in the slideshow gallery. The slideshow gallery
	 * stores these values in its own fields while the new gallery CPT uses the values attached to the
	 * image itself.
	 *
	 * @param $slideshow_gallery
	 */
	protected function _update_images( $slideshow_gallery ) {

		$slug = $slideshow_gallery[ 'slug' ];

		$this->_write_log( sprintf( 'Updating images for slideshow gallery "%s"...', $slug ) );

		// get the slideshow image fields

		$images = $slideshow_gallery[ 'images' ];

		// loop through the image fields to look up the corresponding image posts

		$count = 0;

		if ( ! empty( $images ) && is_array( $images ) ) {

			foreach ( $images as $image ) {

				// get the image post

				$attachment = get_post( $image[ 'image' ] );

				// update the image post fields

				$this->_update_image_data( $attachment, $image );
				$this->_update_image_meta( $attachment, $image );

				$count ++;

			}

		}

		$this->_write_log( sprintf( 'Updated %d images for slideshow gallery "%s"', $count, $slug ) );

	}

	/**
	 * Updates the blank fields for the given image post.
	 *
	 * @param \WP_Post $attachment
	 * @param array $image
	 *
	 */
	protected function _update_image_data( $attachment, $image ) {

		$this->_write_log( sprintf( 'Updating data for image %d...', $attachment->ID ) );

		$data = [ ];

		// See if Title needs to be updated

		if ( empty( $attachment->post_title ) ) {
			$data[ 'post_title' ] = ( ! empty( $image[ 'image_title' ] ) ) ? $image[ 'image_title' ] : '';
		}

		// See if Caption needs to be updated

		if ( empty( $attachment->post_excerpt ) ) {
			$data[ 'post_excerpt' ] = ( ! empty( $image[ 'image_caption' ] ) ) ? $image[ 'image_caption' ] : '';;
		}

		// If updates were required, add the ID and update the post.

		if ( ! empty( $data ) ) {
			$data[ 'ID' ] = $attachment->ID;

			if ( ! $this->dry_run ) {
				wp_update_post( $data );
			}
		}

		$this->_write_log( sprintf( 'Updated data for image %d', $attachment->ID ) );

	}

	/**
	 * Updates the blank meta data for the given image post.
	 *
	 * @param \WP_Post $attachment
	 * @param array $image
	 */
	protected function _update_image_meta( $attachment, $image ) {

		$this->_write_log( sprintf( 'Updating meta data for image %d...', $attachment->ID ) );

		// update the Alt text if not already set

		$alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );

		if ( empty( $alt ) && ! empty( $image[ 'image_alt' ] ) ) {
			if ( ! $this->dry_run ) {
				update_post_meta( $attachment->ID, '_wp_attachment_image_alt', $image[ 'image_alt' ] );
			}
		}

		// update the Credit text if not already set

		$credit = get_post_meta( $attachment->ID, '_image_credit', true );

		if ( empty( $credit ) ) {

			// slideshow galleries porior to 2017-06-01 stored image credit in the alt field. This is a legacy
			// of the old Robb Report site. So check the post date and set credit as appropriate.

			if ( get_the_date( 'U', $attachment->ID ) < strtotime( '2017-06-01' ) ) {
				$credit = ( ! empty( $image[ 'image_alt' ] ) ) ? $image[ 'image_alt' ] : '';
			} else {
				$credit = ( ! empty( $image[ 'image_credit' ] ) ) ? $image[ 'image_credit' ] : '';
			}

			if ( ! $this->dry_run ) {
				update_post_meta( $attachment->ID, '_image_credit', $credit );
			}

		}

		$this->_write_log( sprintf( 'Updated meta data for image %d', $attachment->ID ) );

	}

	/**
	 * Creates a gallery item using the data from the given slideshow gallery
	 * and returns the new gallery item's ID.
	 *
	 * Note that slideshow gallery is not a post type - it is a slideshow field - and so
	 * there are no post meta data to copy here.
	 *
	 * @param $slideshow_gallery
	 * @param int $slideshow_id
	 * @return int
	 */
	protected function _create_gallery_item( $slideshow_gallery, $slideshow_id ) {

		$gallery_item_id = $this->_add_gallery_item_post( $slideshow_gallery, $slideshow_id );
		$this->_add_gallery_item_post_authors( $slideshow_gallery, $gallery_item_id );

		return $gallery_item_id;

	}

	/**
	 * Converts the given slideshow to a gallery, by changing its post type
	 * and adding gallery items as meta data.
	 *
	 * @param int $slideshow_id         ID of the slideshow to convert
	 * @param array $gallery_item_ids   IDs of gallery items to add to the new gallery
	 */
	protected function _convert_slideshow( $slideshow_id, $gallery_item_ids ) {

		$this->_write_log( sprintf( 'Converting slideshow, ID: %d...', $slideshow_id ) );

		if ( ! $this->dry_run ) {
			set_post_type( $slideshow_id, Gallery::POST_TYPE );
			if ( isset( $gallery_item_ids ) && ! empty( $gallery_item_ids ) ) {
				update_post_meta( $slideshow_id, 'gallery_items', [ 'ids' => $gallery_item_ids ] );
			}
		}

		$this->_write_log( sprintf( 'Converted slideshow, ID: %d', $slideshow_id ) );

	}

	/**
	 * Inserts a gallery item post using the data from the
	 * given slideshow gallery.
	 *
	 * @param $slideshow_gallery
	 * @param int $slideshow_id
	 * @return int|\WP_Error
	 */
	protected function _add_gallery_item_post( $slideshow_gallery, $slideshow_id ) {

		$this->_write_log( 'Inserting a new gallery item post...' );

		// get the image IDs so we can build a gallery short code

		$images = ( ! empty( $slideshow_gallery[ 'images' ] ) ) ? $slideshow_gallery[ 'images' ] : [];
		$image_ids = [ ];

		if ( ! empty( $images ) ) {
			foreach ( $images as $image ) {
				if ( ! empty( $image[ 'image' ] ) ) {
					$image_ids[] = $image[ 'image' ];
				}
			}
		}

		// put the data together

		$body = ( ! empty( $slideshow_gallery[ 'body' ] ) ) ? $slideshow_gallery[ 'body' ] : '';
		$gallery_shortcode = ( ! empty( $image_ids ) ) ? '[gallery ids="' . implode( ',', $image_ids ) . '"]' : '';

		$data = [
			'post_type'    => Gallery_Item::POST_TYPE,
			'post_name'    => ( ! empty( $slideshow_gallery[ 'slug' ] ) ) ? $slideshow_gallery[ 'slug' ] : '',
			'post_title'   => ( ! empty( $slideshow_gallery[ 'title' ] ) ) ? $slideshow_gallery[ 'title' ] : '',
			'post_status'  => 'publish',
			'post_content' => $gallery_shortcode . $body,
		];

		// insert a gallery item post

		$gallery_item_id = 0;

		if ( ! $this->dry_run ) {
			$gallery_item_id = wp_insert_post( $data );
			update_post_meta( $gallery_item_id, 'parent_gallery', [ 'id' => $slideshow_id ] );
		}

		$this->_write_log( sprintf( 'Inserted gallery item post, ID: %d', $gallery_item_id ) );

		return $gallery_item_id;
	}

	/**
	 * Adds authors to a gallery item using data from the given slideshow gallery.
	 *
	 * @param $slideshow_gallery
	 * @param int $gallery_item_id
	 */
	protected function _add_gallery_item_post_authors( $slideshow_gallery, $gallery_item_id ) {

		global $coauthors_plus;

		$this->_write_log( 'Inserting gallery item post authors...' );

		$authors = $this->_get_slideshow_gallery_authors( $slideshow_gallery );

		if ( ! empty( $authors ) && ! $this->dry_run ) {
			$coauthors_plus->add_coauthors( $gallery_item_id, $authors, false );
		}

		$this->_write_log( sprintf( 'Inserted %d authors for gallery item, ID: %d', count( $authors ), $gallery_item_id ) );

	}

	/**
	 * Returns the given slideshow gallery's authors as an array of user logins.
	 *
	 * Note: a slideshow gallery's authors are stored as taxonomy term IDs. However, authors
	 * must be added to the gallery item post as user logins. So here we use the taxonomy
	 * terms to get the corresponding user logins.
	 *
	 * @param $slideshow_gallery
	 * @return array
	 */
	protected function _get_slideshow_gallery_authors( $slideshow_gallery ) {

		$authors = [ ];

		$term_ids = ( ! empty( $slideshow_gallery[ 'authors' ] ) ) ? $slideshow_gallery[ 'authors' ] : [];

		if ( ! empty( $term_ids ) && is_array( $term_ids ) ) {
			foreach ( $term_ids as $term_id ) {
				$term = get_term( $term_id, 'author' );
				if ( isset( $term->name ) ) {
					$authors[] = $term->name;
				}
			}
		}

		return $authors;
	}

	/**
	 * The main rollback loop.
	 *
	 * @param $args
	 * @param array $assoc_args
	 */
	public function rollback( $args, $assoc_args = [ ] ) {

		$this->_extract_common_args( $assoc_args );
		\WP_CLI::confirm( sprintf( 'Ok to proceed with the %s rollback?', ( $this->dry_run ) ? 'DRY RUN' : 'LIVE' ), $assoc_args );

		$offset = 0;
		$gallery_ids = $this->_get_gallery_ids( $offset );

		while( ! empty( $gallery_ids ) ) {

			// convert the current batch of galleries back to slideshows

			$this->_rollback_galleries( $gallery_ids );

			// get the next batch

			$this->stop_the_insanity();
			sleep( 1 );
			$offset += self::BATCH_SIZE;
			$gallery_ids = $this->_get_gallery_ids( $offset );

		}

		if ( ! $this->dry_run ) {
			pmc_update_option( PMC_SLIDESHOWS_CONVERTED, 'no' );
		}

		$this->_success( 'The rollback process is finished.' );

	}

	/**
	 * Returns the IDs of a batch of gallery posts.
	 *
	 * Note: there is no need to use WP_Query's 'offset' parameter to skip over
	 * records because we're changing the post type when each batch is converted
	 * and so the converted items will automatically be excluded from the query.
	 *
	 * @param int $offset
	 * @return array
	 */
	protected function _get_gallery_ids( $offset ) {

		$this->_write_log( sprintf( 'Getting batch %d of galleries...', ( $offset / self::BATCH_SIZE  ) ) );

		// Don't use offset in live mode:
		// we're changing the post type as each batch is processed and since post type is one of the query's
		// selection criteria, the converted items will be automatically excluded from the results.

		if ( ! $this->dry_run ) {
			$offset = 0;
		}

		$query = new \WP_Query( [
			'post_type'        => Gallery::POST_TYPE,
			'posts_per_page'   => self::BATCH_SIZE,
			'fields'           => 'ids',
			'offset'           => $offset,
		] );

		if ( is_wp_error( $query ) ) {
			$this->_error( $query->get_error_message() );
		}

		$this->_write_log( sprintf( 'Found %d galleries', count( $query->posts ) ) );

		return $query->posts;

	}

	/**
	 * Changes galleries back to slideshows.
	 *
	 * @param array $gallery_ids  IDs of the galleries to roll back
	 */
	protected function _rollback_galleries( $gallery_ids ) {

		$this->_write_log( 'Rolling back galleries...' );

		if ( empty( $gallery_ids ) ) {
			$this->_write_log( 'Did not find any galleries to roll back' );
			return;
		}

		$count = 0;

		foreach ( $gallery_ids as $gallery_id ) {
			$this->_rollback_gallery( $gallery_id );
			$count ++;
		}

		$this->_write_log( sprintf( 'Rolled back %d galleries', $count ) );

	}

	/**
	 * Changes the given gallery back to a slideshow, by changing its post type
	 * and deleting its attached gallery items.
	 *
	 * @param int $gallery_id
	 */
	protected function _rollback_gallery( $gallery_id ) {

		$this->_write_log( sprintf( 'Rolling back gallery, ID: %d...', $gallery_id ) );

		$gallery_item_ids = get_post_meta( $gallery_id, 'gallery_items', false );

		if ( ! empty( $gallery_item_ids[ 0 ][ 'ids' ] ) ) {
			$this->_delete_gallery_items( $gallery_id, $gallery_item_ids[ 0 ][ 'ids' ] );
		}

		if ( ! $this->dry_run ) {
			set_post_type( $gallery_id, Listicle_Slideshow::POST_TYPE );
		}

		$this->_write_log( sprintf( 'Rolled back gallery, ID: %d', $gallery_id ) );
	}

	/**
	 * Deletes gallery item posts.
	 *
	 * @param int $gallery_id             ID of the parent gallery
	 * @param array $gallery_item_ids     IDs of the gallery items to be deleted
	 */
	protected function _delete_gallery_items( $gallery_id, $gallery_item_ids ) {

		$this->_write_log( sprintf( 'Deleting gallery items for gallery %d...', $gallery_id ) );

		$count = 0;

		// delete the gallery item posts

		if ( is_array( $gallery_item_ids ) && ! empty( $gallery_item_ids ) ) {
			foreach ( $gallery_item_ids as $gallery_item_id ) {
				if ( ! $this->dry_run ) {
					wp_delete_post( $gallery_item_id );
				}
				$this->_write_log( sprintf( 'Deleted gallery item, ID: %d', $gallery_item_id ) );
				$count ++;
			}
		}

		// delete the gallery item meta data

		if ( ! $this->dry_run ) {
			delete_post_meta( $gallery_id, 'gallery_items' );
		}

		$this->_write_log( sprintf( 'Deleted %d gallery items for gallery %d', $count, $gallery_id ) );

	}

	/**
	 * Sets the conversion flag to 'yes - for testing only.
	 *
	 */
	public function force_convert( ) {

		pmc_update_option( PMC_SLIDESHOWS_CONVERTED, 'yes' );
		$this->_success( 'Conversion flag is set to yes.' );

	}

	/**
	 * Sets the conversion flag to 'no' - for testing only.
	 *
	 */
	public function force_rollback( ) {

		pmc_update_option( PMC_SLIDESHOWS_CONVERTED, 'no' );
		$this->_success( 'Conversion flag is set to no.' );

	}

}
