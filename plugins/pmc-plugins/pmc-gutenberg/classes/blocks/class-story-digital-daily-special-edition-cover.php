<?php
/**
 * Cover block for Digital Daily Special Edition articles.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\Blocks;

use PMC\Digital_Daily\CPT;
use const PMC\Digital_Daily\POST_TYPE_SPECIAL_EDITION_ARTICLE;
use PMC\Digital_Daily\Full_View;
use PMC\Gutenberg\Story_Block_Engine;
use PMC\Gutenberg\Interfaces\Block_Base\With_Dependencies;
use PMC\Gutenberg\Traits\Block_Base\Digital_Daily_Common_Elements;
use PMC\Larva;

/**
 * Class Story_Digital_Daily_Special_Edition_Cover.
 */
class Story_Digital_Daily_Special_Edition_Cover extends Story_Block_Engine implements With_Dependencies {
	/**
	 * Reusable pattern elements shared across Digital Daily blocks.
	 */
	use Digital_Daily_Common_Elements;

	/**
	 * Story_Digital_Daily_Special_Edition_Cover constructor.
	 */
	public function __construct() {
		$this->story_block_config = [
			POST_TYPE_SPECIAL_EDITION_ARTICLE => [
				'postType'     => POST_TYPE_SPECIAL_EDITION_ARTICLE,
				'taxonomySlug' => 'category',
				'viewMoreText' => '', // Block links to embedded article.
			],
		];

		$this->larva_module = 'block-special-cover';

		$this->create_story_block(
			'story-digital-daily-special-edition-cover'
		);
	}

	/**
	 * Called by the `render_block` method of the main `Gutenberg` class.
	 *
	 * @codeCoverageIgnore Class will be refactored into Larva controllers after
	 *                     Patterns are moved to `pmc-larva`. Until then, method
	 *                     is untestable as prototype data is not available.
	 *
	 * @param string $block_content The block content about to be appended.
	 * @param array $block The full block, including name and attributes.
	 * @return array|null Data that can be used with `PMC::render_template` or
	 *                    null to prevent block from rendering.
	 */
	public function larva_data( string $block_content, array $block ): ?array {
		$data = parent::larva_data( $block_content, $block );

		if ( ! $this->_attributes['postID'] ) {
			return null;
		}

		$permalink = $this->_get_permalink();

		$this->_set_permalink_attr( $data['special_cover_block_permalink_attr'] );
		$this->_set_title_attr( $data['special_cover_block_title_attr'] );

		if ( $this->_attributes['featuredImageID'] ) {
			Larva\add_controller_data(
				Larva\Controllers\Objects\O_Figure::class,
				[
					'image_id'   => $this->_attributes['featuredImageID'],
					'image_size' => 'digital-daily-3-4',
					'post_id'    => $this->_attributes['postID'],
				],
				$data['o_figure']
			);

			$data['o_figure']['o_figure_link_url']                     =
				$permalink;
			$data['o_figure']['c_lazy_image']['c_lazy_image_link_url'] =
				$permalink;
		} else {
			$data['o_figure'] = false;
		}

		Larva\add_controller_data(
			Larva\Controllers\Components\C_Title::class,
			[
				'post_id' => $this->_attributes['postID'],
				'title'   => $data['c_title']['c_title_markup'],
			],
			$data['c_title']
		);
		$data['c_title']['c_title_url']     = $permalink;
		$data['c_title']['c_title_id_attr'] = '';

		$this->_conditionally_add_c_author( $data );

		$this->_add_c_paragraph( $data['c_paragraph'], 1 );

		$data['c_button']['c_button_url'] = $permalink;

		return $data;
	}

	/**
	 * Build permalink that links to Special Edition article block on the
	 * current view. Unlike all other permalinks, on the Landing Page view, we
	 * do not link to the Full View, but rather to the SEA block in the current
	 * view.
	 *
	 * @return string
	 */
	protected function _get_permalink(): string {
		if ( Full_View::is() ) {
			$permalink = Full_View::get_permalink( get_queried_object_id() );
		} else {
			$added = Full_View::permalink_filters_added();

			if ( $added ) {
				Full_View::remove_permalink_filters();
			}

			$permalink = get_permalink( get_queried_object_id() );

			if ( $added ) {
				Full_View::add_permalink_filters();
			}
		}

		$hash = Larva\get_id_attribute_for_post_id(
			$this->_attributes['postID']
		);

		return sprintf(
			'%1$s#%2$s',
			$permalink,
			$hash
		);
	}

	/**
	 * Return block's Larva module and variant.
	 *
	 * `Digital_Daily_Common_Elements` trait overrides this method to toggle
	 * Larva module for full view, which this block does not have. PHP provides
	 * no way to bypass trait method for the parent it overrides, so we have to
	 * duplicate the method here.
	 *
	 * Method signature must match parent.
	 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	protected function _get_larva_module_with_variant( array $attrs ): string {
		// phpcs:enable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		return sprintf(
			'%1$s.%2$s',
			$this->larva_module,
			$this->larva_module_variant
		);
	}

	/**
	 * Specify classes that must be loaded for this block to be available.
	 *
	 * @return array
	 */
	public function get_dependent_classes(): array {
		return [
			CPT::class,
		];
	}
}
