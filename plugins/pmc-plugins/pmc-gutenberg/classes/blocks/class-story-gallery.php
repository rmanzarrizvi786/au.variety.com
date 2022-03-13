<?php
/**
 * Story block for PMC Gallery v4.
 *
 * TODO: port Patterns to `pmc-larva` from `wwd-2021`, introduce module controllers.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\Blocks;

use PMC\Digital_Daily\Full_View;
use PMC\Gallery\Defaults;
use PMC\Gutenberg\Story_Block_Engine;
use PMC\Gutenberg\Interfaces\Block_Base\With_Dependencies;
use PMC\Gutenberg\Traits\Block_Base\Digital_Daily_Common_Elements;
use PMC\Larva;

/**
 * @codeCoverageIgnore Class will be refactored into Larva controllers after
 *                     Patterns are moved to `pmc-larva`.
 */
class Story_Gallery extends Story_Block_Engine implements With_Dependencies {
	/**
	 * Reusable pattern elements shared across Digital Daily blocks.
	 */
	use Digital_Daily_Common_Elements;

	/**
	 * Gallery items from meta.
	 *
	 * @var array
	 */
	protected array $_gallery_items;

	/**
	 * Configure block.
	 */
	public function __construct() {
		$this->larva_module = 'block-gallery';

		$this->story_block_config = [
			Defaults::NAME => [
				'postType'     => Defaults::NAME,
				'taxonomySlug' => 'category',
				'viewMoreText' => '',
			],
		];

		$this->create_story_block( 'story-gallery' );
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
			case 'block-gallery':
				$this->_conditionally_add_c_author( $data );

				if ( $this->_attributes['hasDisplayedPrimaryTerm'] ) {
					$this->_add_c_tag_span( $data['c_tag_span'] );
				} else {
					$data['c_tag_span'] = false;
				}

				$this->_add_c_button( $data['c_button'] );
				break;

			case 'block-gallery-full':
				$this->_add_c_article_tags( $data['c_article_tags'] );
				$this->_add_c_tag_span( $data['c_tag_span'] );
				$data['c_title']['c_title_url'] = false;

				$data['gallery_post_id'] = $this->_attributes['postID'];

				$this->_populate_sharing_buttons( $data );
				break;

			default:
				$data = null;
				break;
		}

		$this->_gallery_items = [];

		$this->_conditionally_restore_full_view_filters( $removed );

		return $data;
	}

	/**
	 * Get gallery items.
	 */
	protected function _get_images(): void {
		// Disallowed only because they have little use in Core (https://wp.me/p2AvED-gCU).
		// phpcs:disable WordPress.PHP.DisallowShortTernary
		$this->_gallery_items = get_post_meta(
			$this->_attributes['postID'],
			Defaults::NAME,
			true
		) ?: [];

		$this->_gallery_items = array_values( $this->_gallery_items );
	}

	/**
	 * Add post data shared across all variations.
	 *
	 * @param array $data Larva Pattern data.
	 */
	protected function _add_common_data( array &$data ): void {
		$this->_add_c_paragraph( $data['c_paragraph'], 1 );
		$this->_add_c_title( $data['c_title'] );
		$this->_set_permalink_attr( $data['gallery_permalink_attr'] );
		$this->_set_title_attr( $data['gallery_title_attr'] );

		if ( $this->_attributes['hasDisplayedExcerpt'] ) {
			$this->_add_c_dek( $data['c_dek'] );
		}

		if ( $this->_attributes['backgroundColorClassSuffix'] ) {
			$data['gallery_classes'] .= ' lrv-u-background-color-' .
				$this->_attributes['backgroundColorClassSuffix'];
		}

		$data['gallery_overlay_link_url'] = get_permalink(
			$this->_attributes['postID']
		);
		$data['counter']['c_span_text']   = sprintf(
			/* translators: 1. Number of items in gallery. */
			__(
				'%1$s Photos',
				'pmc-gutenberg'
			),
			count( $this->_gallery_items )
		);

		$this->_add_images( $data );
	}

	/**
	 * Populate gallery images in `o-figure` elements.
	 *
	 * @param array $data Pattern data.
	 */
	protected function _add_images( array &$data ): void {
		$quantity  = 'block-gallery-full' === $this->larva_module ? 4 : 3;
		$image_ids = array_slice( $this->_gallery_items, 0, $quantity );
		$first_id  = array_shift( $image_ids );

		$template              = $data['o_figure'];
		$data['gallery_items'] = [];

		// Populate the larger, first image.
		Larva\add_controller_data(
			Larva\Controllers\Objects\O_Figure::class,
			[
				'post_id'  => $this->_attributes['postID'],
				'image_id' => $first_id,
			],
			$data['o_figure_first']
		);

		$data['gallery_items'][] = $data['o_figure_first'];

		// Add `o-figure` that appears under button linking to full gallery.
		Larva\add_controller_data(
			Larva\Controllers\Objects\O_Figure::class,
			[
				'post_id'  => $this->_attributes['postID'],
				'image_id' => $image_ids[ array_key_last( $image_ids ) ],
			],
			$data['o_figure']
		);

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

			$data['gallery_items'][] = $entry;
		}
	}

	/**
	 * Specify classes that must be loaded for this block to be available.
	 *
	 * @return array
	 */
	public function get_dependent_classes(): array {
		return [
			Defaults::class,
			Full_View::class,
		];
	}
}
