<?php
/**
 * This file contains the PMC\Mobile_API\Endpoints\Objects\Gallery_Object class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Objects;

use PMC\Mobile_API\Endpoints\Schema_Definitions\Image;
use PMC\Mobile_API\Route_Registrar;
use WP_Post;

/**
 * Gallery object.
 */
class Gallery_Object {

	/**
	 * Gallery object.
	 *
	 * @var WP_Post
	 */
	protected $gallery_post;

	/**
	 * Gallery_Object constructor.
	 *
	 * @param WP_Post $gallery_post Gallery post object.
	 */
	public function __construct( WP_Post $gallery_post ) {
		$this->gallery_post = $gallery_post;
	}

	/**
	 * Get linked gallery from a post.
	 *
	 * @param int $post_id ID of the post to pull the gallery from.
	 * @return self|null
	 */
	public static function get_gallery_from_post( int $post_id ): ?self {

		// Get linked gallery.
		if ( class_exists( 'PMC' ) ) {
			$gallery = \PMC::get_linked_gallery( $post_id );
		}

		// Bail if empty.
		if ( empty( $gallery->id ) ) {
			return null;
		}

		// Get gallery post.
		$gallery_post = \get_post( $gallery->id );

		// Bail early.
		if ( ! $gallery_post instanceof WP_Post ) {
			return null;
		}

		return new self( $gallery_post );
	}

	/**
	 * Get gallery card response.
	 *
	 * @return array
	 */
	public function get_gallery_card(): array {

		// Get gallery items.
		$items = $this->items();

		return [
			'id'          => $this->gallery_post->ID,
			'images'      => $items,
			'image-count' => count( $items ),
			'link'        => \rest_url( '/' . Route_Registrar::NAMESPACE . sprintf( '/gallery/%d', $this->gallery_post->ID ) ),
		];
	}

	/**
	 * Returns the number of gallery items.
	 *
	 * @return integer
	 */
	public function get_items_count(): int {
		// Values are the attachment ids.
		return count( array_values( $this->get_gallery_variant_ids() ) );
	}

	/**
	 * Get gallery images.
	 *
	 * @return array
	 */
	public function items(): array {

		// Get gallery ids with PMC global class.
		if ( ! class_exists( 'PMC' ) ) {
			return [];
		}

		$items  = array_values( $this->get_gallery_variant_ids() );

		return array_map(
			function( $image_id ) {
				return Image::get_image( (int) $image_id, (array) $this->get_variant_data( $image_id ) );
			},
			(array) $items
		);
	}

	/**
	 * Get variant data.
	 *
	 * @param int $attachment_id Gallery attachment ID.
	 * @return array
	 */
	public function get_variant_data( $attachment_id ): array {

		// Check if the attachment class exists.
		if ( ! class_exists( '\PMC\Gallery\Attachment_Detail' ) ) {
			return [];
		}

		// Use the attachment detail class.
		$gallery_attachment = \PMC\Gallery\Attachment_Detail::get_instance();

		// Get the multi-dimensional array with gallery and variant ids.
		$gallery_meta = $this->get_gallery_variant_ids();
		if ( empty( $gallery_meta ) ) {
			return [];
		}

		// Get variant from the list.
		$variant_id = array_search( $attachment_id, (array) $gallery_meta, true );

		// Get variant custom information.
		$variant_meta = $gallery_attachment->get_variant_meta( $variant_id );

		// Bail early.
		if ( empty( $variant_meta ) ) {
			return [];
		}

		return (array) $variant_meta;
	}

	/**
	 * Get gallery images, together with their variants.
	 *
	 * Gallery images are stored as a multi-dimensional array. Basically, this is a list
	 * of variants (a private post type) ids, with attachment ids.
	 *
	 * @return array
	 */
	protected function get_gallery_variant_ids(): array {

		// Check if plugin is active.
		if ( ! class_exists( 'PMC' ) ) {
			return [];
		}

		return \PMC::get_gallery_items( $this->gallery_post->ID ) ?: [];
	}
}
