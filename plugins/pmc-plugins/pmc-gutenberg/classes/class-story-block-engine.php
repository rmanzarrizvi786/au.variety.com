<?php

namespace PMC\Gutenberg;

use PMC\Global_Functions\Utility\Post;
use PMC\Gutenberg\Interfaces\Block_Base\With_Larva_Data;
use PMC\Larva;

/**
 * Story_Block_Engine Abstract Class
 *
 * This class contains functionality shared between blocks that
 * use stories.
 *
 * The subclasses apply configuration in the following format:
 *
 * $this->story_block_config = [
 *      {post_type} => [
 *          'postType'     => {post_type},
 *          'taxonomySlug' => {taxonomy_slug},
 *          'viewMoreText' => __( '{button text}', 'pmc-gutenberg' ),
 *      ],
 *  ];
 *
 * Each item in this array will add an option in select for post type in the block UI.
 */

abstract class Story_Block_Engine extends Block_Base implements With_Larva_Data {
	/**
	 * Larva module used to render this block.
	 *
	 * This can come from config, if needed in the future.
	 *
	 * @var string
	 */
	public string $larva_module = 'story';

	/**
	 * Variant of Larva module used for this block instance.
	 *
	 * If an implementing class overrides this, it should be careful to reset
	 * the value as is appropriate for its usage.
	 *
	 * @var string
	 */
	public string $larva_module_variant = 'prototype';

	/**
	 * Block configuration.
	 *
	 * @var array
	 */
	public array $story_block_config = [];

	/**
	 * Formatted, block-scoped names for filters etc.
	 *
	 * @var array
	 */
	public array $names = [];

	/**
	 * Block's parsed attributes.
	 *
	 * @var array|null
	 */
	protected ?array $_attributes = null;

	/**
	 * A function to create new story blocks based on localized data from the theme.
	 *
	 * Note: Each block using this engine should must contain its own corresponding JS file.
	 *
	 * @param string The name of the block, after the pmc/ namespace.
	 **/
	public function create_story_block( string $block_name ) : void {

		$this->_block = $block_name;

		$this->_block_args = [
			'editor_style' => 'block-story',
		];

		$this->template = 'modules/' . $this->larva_module;

		$this->names = $this->get_formatted_names();

		$this->register_style();

		add_action( 'enqueue_block_editor_assets', [ $this, 'localize_data' ] );

	}

	/**
	 * Style the block in the editor
	 *
	 * Idea: This could be moved to Block_Base
	 **/
	public function register_style() : void {
		// Note: block CSS is not compiled, so we include it directly from src.
		$block_css = PMC_GUTENBERG_PLUGIN_URL .
			'src/blocks/story/story.css';

		$block_css_path = PMC_GUTENBERG_PLUGIN_PATH .
			'src/blocks/story/story.css';

		$filemtime = filemtime( $block_css_path );

		// Same stylesheet for all story blocks using this system
		wp_register_style(
			'block-story',
			$block_css,
			array( 'wp-edit-blocks' ),
			$filemtime
		);
	}

	/**
	 * Localize data for configuration.
	 *
	 * @see get_formatted_names()
	 */
	public function localize_data() : void {

		// Examples for `story-gallery` block:
		// - data: window.pmc_story_gallery_block_config
		// - filter: pmc_gutenberg_story_gallery_block_config
		wp_localize_script(
			'block-' . $this->_block,
			$this->names['localized_data_name'],
			apply_filters(
				$this->names['localized_data_filter_name'],
				$this->story_block_config
			)
		);

	}

	/**
	 * Return an array of formatted names to be used when handling
	 * configuration for blocks created with this system.
	 *
	 * Idea: Consider moving this to Block_Base if useful for other blocks,
	 * or to be part of localize_data, but cleaner to test on its own.
	 *
	 * @see src/blocks/helpers/config.js
	 *
	 * @return array a list of names to be used in this class and for filters
	 */
	public function get_formatted_names() : array {
		$block_snake_case = str_replace( '-', '_', $this->_block );

		$localized_data_name = sprintf(
			'pmc_%s_block_config',
			$block_snake_case
		);

		$localized_data_filter_name = sprintf(
			'pmc_gutenberg_%s_block_config',
			$block_snake_case
		);

		return [
			'localized_data_name'        => $localized_data_name,
			'localized_data_filter_name' => $localized_data_filter_name,
		];
	}

	/**
	 * Called by the `render_block` method of the main `Gutenberg` class.
	 *
	 * @param string $block_content The block content about to be appended.
	 * @param array $block The full block, including name and attributes.
	 *
	 * @return array|null Data that can be used with `PMC::render_template` or
	 *                    null to prevent block from rendering.
	 */
	public function larva_data( string $block_content, array $block ) : ?array {
		$this->_attributes = $this->_parse_attributes( $block['attrs'] );

		if ( empty( $this->_attributes ) ) {
			return null;
		}

		/**
		 * Set up the data from attributes and assign fallbacks where needed.
		 *
		 * Idea: This can be moved into a separate function, such as story_data_provider
		 * that can be called inside larva_data where it would be plugged into the
		 * pattern keys.
		 **/

		// Content overrides
		$title_text = $this->_attributes['title']
			?? get_the_title( $this->_attributes['postID'] );

		$excerpt_text = $this->_attributes['excerpt']
			?? get_the_excerpt( $this->_attributes['postID'] );

		$permalink = get_permalink( $this->_attributes['postID'] );

		if ( $this->_attributes['hasDisplayedPrimaryTerm'] ) {
			$terms     = get_the_terms(
				$this->_attributes['postID'],
				$this->_attributes['taxonomySlug']
			);
			$term_text = ! is_wp_error( $terms ) && isset( $terms[0] ) ? $terms[0]->name : '';
		} else {
			$term_text = false;
		}

		/**
		 * Prepare the data object for the Larva module
		 *
		 * In the future, this could be separated from the above into a different method
		 * that accepts an object of the actual data (above) and merges it into the larva
		 * module object.
		 **/

		$json_path = sprintf(
			'modules/%s',
			$this->_get_larva_module_with_variant( $block['attrs'] )
		);

		$data = Larva\Pattern::get_json_data( $json_path );

		$data['c_link_bottom']['c_link_url']  = $permalink;
		$data['c_link_bottom']['c_link_text'] = $this->_attributes['viewMoreText'];

		$data['c_title']['c_title_markup'] = $title_text;
		$data['c_title']['c_title_url']    = $permalink;
		$data['c_title']['c_title_text']   = '';

		// Hide span if text is empty
		if ( empty( $term_text ) ) {
			$data['c_span'] = false;
		} else {
			$data['c_span']['c_span_text'] = $term_text;
		}

		if ( false === $this->_attributes['hasDisplayedExcerpt'] ) {
			$data['c_dek'] = false;
		} else {
			$data['c_dek']['c_dek_text']   = false;
			$data['c_dek']['c_dek_markup'] = $excerpt_text;
		}

		if ( isset( $data['c_lazy_image'] ) ) {
			Larva\add_controller_data(
				Larva\Controllers\Components\C_Lazy_Image::class,
				[
					'image_id' => $this->_attributes['featuredImageID'],
					'post_id'  => $this->_attributes['postID'],
				],
				$data['c_lazy_image']
			);
		}

		return $data;
	}

	/**
	 * Parse block attributes along with defaults.
	 *
	 * @param array $attributes Block attributes.
	 * @return array|null
	 */
	protected function _parse_attributes( array $attributes ): ?array {
		if ( empty( $attributes['postID'] ) ) {
			return null;
		}

		$parsed = [
			'postID' => (int) $attributes['postID'],
		];

		if ( ! $this->_can_render( $parsed['postID'], $attributes ) ) {
			return null;
		}

		$post_type = get_post_type( $parsed['postID'] );

		if ( ! empty( $attributes['featuredImageID'] ) ) {
			$parsed['featuredImageID'] = (int) $attributes['featuredImageID'];
		} elseif ( has_post_thumbnail( $parsed['postID'] ) ) {
			$parsed['featuredImageID'] =
				get_post_thumbnail_id( $parsed['postID'] );
		} else {
			$parsed['featuredImageID'] = null;
		}

		$parsed['hasDisplayedExcerpt'] = $attributes['hasDisplayedExcerpt']
			?? true;

		$parsed['hasDisplayedByline'] = $attributes['hasDisplayedByline']
			?? true;

		$parsed['hasDisplayedPrimaryTerm'] = $attributes['hasDisplayedPrimaryTerm']
			?? true;

		if ( ! empty( $attributes['excerpt'] ) ) {
			// String will be passed through `esc_html()`, so we remove tags.
			$parsed['excerpt'] = wp_strip_all_tags(
				$attributes['excerpt']
			);
		} else {
			$parsed['excerpt'] = null;
		}

		if ( ! empty( $attributes['title'] ) ) {
			$parsed['title'] = $attributes['title'];
		} else {
			$parsed['title'] = null;
		}

		$parsed['viewMoreText'] = $attributes['viewMoreText']
			?? $this->story_block_config[ $post_type ]['viewMoreText']
			?? __( 'View', 'pmc-gutenberg' );

		$parsed['taxonomySlug'] = $attributes['taxonomySlug']
			?? $this->story_block_config[ $post_type ]['taxonomySlug']
			?? 'category';

		return $parsed;
	}

	/**
	 * Story Block Engine includes unpublished and draft posts in case one
	 * needs to create a scheduled Gutenberg-enabled post object that
	 * includes articles that are scheduled for publish at the same time.
	 *
	 * @param int    $post_id          Post ID chosen for this block.
	 * @param array  $block_attributes Story block's attributes.
	 * @return bool
	 */
	final protected function _can_render(
		int $post_id,
		array $block_attributes
	): bool {
		return apply_filters(
			'pmc_gutenberg_story_block_engine_can_render_post',
			Post::is_accessible_by_current_user( $post_id ),
			$post_id,
			$this->_block,
			$block_attributes
		);
	}

	/**
	 * Return block's Larva module and variant.
	 *
	 * Variable is important to implementing classes.
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
}
