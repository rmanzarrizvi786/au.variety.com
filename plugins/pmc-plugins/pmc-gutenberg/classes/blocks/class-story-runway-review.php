<?php
/**
 * Story block for WWD Runway Review.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\Blocks;

use PMC\Digital_Daily\Full_View;
use PMC\Global_Functions\Utility\Attachment;
use PMC\Gallery\Defaults;
use PMC\Gutenberg\Story_Block_Engine;
use PMC\Gutenberg\Interfaces\Block_Base\With_Dependencies;
use PMC\Gutenberg\Traits\Block_Base\Digital_Daily_Common_Elements;
use PMC\Larva;
use WWD_Post_Type_Runway_Review;

/**
 * @codeCoverageIgnore Class will be refactored into Larva controllers after
 *                     Patterns are moved to `pmc-larva`.
 */
class Story_Runway_Review extends Story_Block_Engine implements With_Dependencies {
	/**
	 * Reusable pattern elements shared across Digital Daily blocks.
	 */
	use Digital_Daily_Common_Elements;

	/**
	 * ID of linked gallery.
	 *
	 * @var int
	 */
	protected ?int $_gallery_id = null;

	/**
	 * Gallery items from meta.
	 *
	 * @var array
	 */
	protected array $_gallery_items = [];

	/**
	 * Configure block.
	 */
	public function __construct() {
		$this->larva_module = 'block-runway';

		$this->story_block_config = [
			'runway-review' => [
				'postType'     => 'runway-review',
				'taxonomySlug' => 'category',
				'viewMoreText' => '',
			],
		];

		$this->create_story_block( 'story-runway-review' );

		$this->_set_styles( 3 );
	}

	/**
	 * Populate pattern from post data.
	 *
	 * @param string $block_content
	 * @param array  $block
	 * @return array|null
	 */
	public function larva_data( string $block_content, array $block ): ?array {
		$data = parent::larva_data( $block_content, $block );

		if ( null === $data ) {
			return null;
		}

		// Remove extraneous data from parent method.
		unset( $data['c_link'], $data['c_span'] );

		// No full-view representation, so users are allowed to click out of DD.
		$removed = $this->_conditionally_remove_full_view_filters();

		$this->_get_images();

		$this->_add_common_data( $data );

		switch ( $this->larva_module ) {
			case 'block-runway':
				$this->_conditionally_add_c_author( $data );

				$this->_add_c_button( $data['c_button'] );
				$this->_add_c_paragraph( $data['c_paragraph'], 1 );
				break;

			case 'block-runway-full':
				$this->_add_c_article_tags( $data['c_article_tags'] );

				$this->_add_c_tag_span( $data['c_tag_span'] );
				$this->_add_c_timestamp( $data['c_timestamp'] );
				$this->_add_image_credit( $data );
				$data['c_title']['c_title_url'] = false;

				$data['runway_post_id'] = $this->_attributes['postID'];

				$this->_populate_sharing_buttons( $data );
				break;

			default:
				$data = null;
				break;
		}

		$this->_gallery_id    = null;
		$this->_gallery_items = [];

		$this->_conditionally_restore_full_view_filters( $removed );

		return $data;
	}

	/**
	 * Get runway's linked gallery items.
	 */
	protected function _get_images(): void {
		$linked_gallery = get_post_meta(
			$this->_attributes['postID'],
			'pmc-gallery-linked-gallery',
			true
		);

		if ( empty( $linked_gallery ) ) {
			return;
		}

		$linked_gallery = json_decode( $linked_gallery, true );

		if ( ! empty( $linked_gallery['id'] ) ) {
			$this->_gallery_id = (int) $linked_gallery['id'];

			// Disallowed only because they have little use in Core (https://wp.me/p2AvED-gCU).
			// phpcs:disable WordPress.PHP.DisallowShortTernary
			$this->_gallery_items = get_post_meta(
				$this->_gallery_id,
				Defaults::NAME,
				true
			) ?: [];

			$this->_gallery_items = array_values( $this->_gallery_items );
		}
	}

	/**
	 * Add post data shared across all variations.
	 *
	 * @param array $data Larva Pattern data.
	 */
	protected function _add_common_data( array &$data ): void {
		$this->_add_c_title( $data['c_title'] );
		$this->_set_permalink_attr( $data['runway_permalink_attr'] );
		$this->_set_title_attr( $data['runway_title_attr'] );

		if ( $this->_attributes['hasDisplayedExcerpt'] ) {
			$this->_add_c_dek( $data['c_dek'] );
		}

		if ( $this->_attributes['backgroundColorClassSuffix'] ) {
			$data['runway_classes'] .= ' lrv-u-background-color-' .
				$this->_attributes['backgroundColorClassSuffix'];
		}

		$this->_add_images( $data );

		if ( $this->_gallery_id ) {
			$data['o_icon_span_button']['o_button_url'] = get_permalink( $this->_gallery_id );
		}

		$data['o_icon_span_button']['c_span_small']['c_span_text'] = sprintf(
			/* translators: 1. Number of items in gallery. */
			__(
				'%1$s Photos',
				'pmc-gutenberg'
			),
			count( $this->_gallery_items )
		);
	}

	/**
	 * Populate gallery images in `o-figure` elements.
	 *
	 * @param array $data Pattern data.
	 */
	protected function _add_images( array &$data ): void {
		$quantity  = 'block-runway-full' === $this->larva_module ? 4 : 3;
		$image_ids = array_slice( $this->_gallery_items, 0, $quantity );

		$template                    = $data['o_figure'];
		$data['o_runway_list_items'] = [];

		// Populate the larger, first image.
		if ( 'block-runway-full' === $this->larva_module ) {
			$first_id = array_shift( $image_ids );

			if ( empty( $first_id ) ) {
				$data['o_figure_first_featured'] = false;
			} else {
				Larva\add_controller_data(
					Larva\Controllers\Objects\O_Figure::class,
					[
						'post_id'  => $this->_attributes['postID'],
						'image_id' => $first_id,
					],
					$data['o_figure_first_featured']
				);
			}
		}

		// Populate smaller images' `o-figure`.
		foreach ( $image_ids as $image_id ) {
			$entry = $template;

			Larva\add_controller_data(
				Larva\Controllers\Objects\O_Figure::class,
				[
					'post_id'  => $this->_attributes['postID'],
					'image_id' => $image_id,
				],
				$entry
			);

			$data['o_runway_list_items'][] = $entry;
		}

		if ( 'block-runway-full' !== $this->larva_module ) {
			$data['o_figure'] = array_shift(
				$data['o_runway_list_items']
			);
		}
	}

	/**
	 * Populate image credit elements.
	 *
	 * @param array $data Pattern data.
	 */
	protected function _add_image_credit( array &$data ): void {
		$first_image = $this->_gallery_items[0] ?? false;

		$data['image_credit_title']['c_title_text']    = '';
		$data['image_credit_title']['c_title_id_attr'] = '';
		$data['image_credit_title']['c_title_url']     = false;
		$data['image_credit_title']['c_title_markup']  = $first_image
			? wp_get_attachment_caption( $first_image )
			: '';

		$data['image_credit_span']['c_span_text'] = $first_image
			? Attachment::get_instance()->get_image_credit( $first_image )
			: '';

	}

	/**
	 * Specify classes that must be loaded for this block to be available.
	 *
	 * @return array
	 */
	public function get_dependent_classes(): array {
		return [
			Full_View::class,
			WWD_Post_Type_Runway_Review::class,
		];
	}
}
