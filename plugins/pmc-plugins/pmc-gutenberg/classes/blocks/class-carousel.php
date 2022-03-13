<?php
/**
 * Render Carousel Block variations.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\Blocks;

use PMC\Gutenberg\Block_Base;
use PMC\Gutenberg\Interfaces\Block_Base\With_Render_Callback;
use PMC\Larva\Controllers\Modules;
use PMC\Top_Videos_V2\PMC_Top_Videos;
use WP_Block;
use WP_Term;

/**
 * Class Carousel.
 */
class Carousel extends Block_Base implements With_Render_Callback {
	/**
	 * Curation taxonomy representing video playlists.
	 */
	const TAXONOMY_PLAYLIST = 'vcategory';

	/**
	 * Attributes of block instance being rendered.
	 *
	 * @var array
	 */
	protected $_attributes;

	/**
	 * Variant for Pattern assigned based on block style.
	 *
	 * @var string
	 */
	public $variant;

	/**
	 * Block configuration.
	 *
	 * @var array
	 */
	public array $block_config = [];

	public function __construct() {
		$this->_block = 'carousel';

		$this->block_config = [
			'video' => [
				'post_type' => 'pmc_top_video',
			],
		];

		$this->_setup_hooks();
	}

	/**
	 * setting wp hooks
	 */
	protected function _setup_hooks() {
		add_filter( 'pmc_gutenberg_blocks_config', [ $this, 'filter_pmc_gutenberg_blocks_config' ] );
	}

	/**
	 * Render block.
	 *
	 * @codeCoverageIgnore Individual methods are covered, cannot test attribute
	 * setting independently from rendering due to reset before return.
	 *
	 * Declaration must be compatible with interface.
	 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	 *
	 * @param array    $attrs   Block attributes.
	 * @param string   $content Block content.
	 * @param WP_Block $block   Block object.
	 * @return string
	 */
	public function render_callback(
		array $attrs,
		string $content,
		WP_Block $block
	): string {
		// phpcs:enable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		if (
			empty( $attrs['curationTaxonomy'] )
			|| empty( $attrs['termId'] )
		) {
			return '';
		}

		$attrs['termId']   = (int) $attrs['termId'];
		$this->_attributes = $attrs;

		switch ( $this->_attributes['className'] ?? null ) {
			case 'is-style-3-up':
			default:
				$output = $this->_render_carousel_grid( 3 );
				break;

			case 'is-style-4-up':
				$output = $this->_render_carousel_grid( 4 );
				break;

			case 'is-style-gallery':
				$output = $this->_render_gallery();
				break;

			case 'is-style-inline-video':
				$output = $this->_render_inline_video();
				break;

			case 'is-style-story-river':
				$output = $this->_render_story_river();
				break;
		}

		$this->_attributes = null;

		return $output;
	}

	/**
	 * Retrieve data for Carousel Grid.
	 *
	 * @param int $num_posts Number of posts to render.
	 * @return string
	 */
	protected function _render_carousel_grid( int $num_posts ): string {
		$posts = $this->_get_carousel_posts( $num_posts );

		if ( null === $posts ) {
			return '';
		}

		return Modules\Carousel_Grid::get_instance()->init(
			[
				'data'    => compact( 'posts' ),
				'variant' => 4 === $num_posts ? 'overlay' : 'prototype',
			]
		)->render();
	}

	/**
	 * Retrieve data for gallery style.
	 *
	 * @return string
	 */
	protected function _render_gallery(): string {
		$posts = $this->_get_carousel_posts( 10 );

		if ( null === $posts ) {
			return '';
		}

		return Modules\Carousel_Slider::get_instance()->init(
			[
				'data'    => compact( 'posts' ),
				'variant' => 'prototype',
			]
		)->render();
	}

	/**
	 * Retrieve data for inline-video-player style.
	 *
	 * @return string
	 */
	protected function _render_inline_video(): string {
		$posts = $this->_get_featured_videos();

		if ( empty( $posts ) ) {
			return '';
		}

		return Modules\Vlanding_Video_Showcase::get_instance()->init(
			[
				'data' => [
					'post_ids' => $posts->posts,
				],
			]
		)->render();
	}

	/**
	 * Retrieve data for story-river style.
	 *
	 * @return string
	 */
	protected function _render_story_river(): string {
		$posts = $this->_get_carousel_posts( 8 );

		if ( null === $posts ) {
			return '';
		}

		return Modules\Story_Grid::get_instance()->init(
			[
				'data'    => compact( 'posts' ),
				'variant' => 'prototype',
			]
		)->render();
	}

	/**
	 * Retrieve PMC Carousel posts for rendering with different
	 * modules/variations.
	 *
	 * @param int $qty Number of posts to retrieve.
	 * @return array|null
	 */
	protected function _get_carousel_posts( int $qty ): ?array {
		$term = get_term( $this->_attributes['termId'] );

		if ( ! $term instanceof WP_Term ) {
			return null;
		}

		$posts = pmc_render_carousel(
			$this->_attributes['curationTaxonomy'],
			$term->slug,
			$qty
		);

		if ( ! empty( $posts ) && is_array( $posts ) ) {
			return $posts;
		}

		// No carousel countent found, let's getting featured videos instead.
		$posts = $this->_get_featured_videos();

		if ( empty( $posts ) ) {
			return null;
		}

		$output = [];

		foreach ( $posts->posts as $count => $post_id ) {
			if ( $count < $qty ) {
				$output[ $post_id ] = [
					'ID' => $post_id,
				];
			}
		}

		return $output;
	}

	/**
	 * Get featured videos in playlist.
	 *
	 * @return object|null
	 */
	protected function _get_featured_videos() : ?object {
		if (
			static::TAXONOMY_PLAYLIST !== $this->_attributes['curationTaxonomy']
		) {
			return null;
		}

		$args = [
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'tax_query'      => [
				[
					'taxonomy' => $this->_attributes['curationTaxonomy'],
					'terms'    => $this->_attributes['termId'],
					'field'    => 'term_id',
				],
			],
			'fields'         => 'ids',
			'posts_per_page' => 100,
			'no_found_rows'  => true,
			'post_status'    => 'publish',
			'order'          => 'DESC',
			'orderby'        => 'post_date',
		];

		$posts = (object) [];

		if ( post_type_exists( 'variety_top_video' ) ) {
			$args['post_type'] = 'variety_top_video';
			$posts             = new \WP_Query( $args );
		} elseif ( class_exists( 'PMC\Top_Videos_V2\PMC_Top_Videos', false ) ) {
			$posts = PMC_Top_Videos::get_instance()->get_posts( $args );
		}

		return $posts;
	}

	/**
	 * Add block configuration to global blocks configuration
	 *
	 * @param $blocks_config
	 *
	 * @return array
	 */
	public function filter_pmc_gutenberg_blocks_config( $blocks_config ) : array {
		$block_config_key  = sprintf( 'pmc_%s_block_config', $this->_block );
		$filter_name       = sprintf( 'pmc_gutenberg_%s_block_config', $this->_block );
		$block_config_data = apply_filters(
			$filter_name,
			$this->block_config
		);

		$this->block_config = $block_config_data;

		$blocks_config[ $block_config_key ] = $block_config_data;

		return $blocks_config;

	}
}
