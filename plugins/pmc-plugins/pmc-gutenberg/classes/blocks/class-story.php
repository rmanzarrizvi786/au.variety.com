<?php

namespace PMC\Gutenberg\Blocks;

use PMC\Gutenberg\Story_Block_Engine;
use PMC\Larva\Controllers\Components;

class Story extends Story_Block_Engine {

	/**
	 * Set configuration and create the story block.
	 *
	 * @see Story_Block_Engine::localize_data
	 */
	protected function __construct() {

		// Filterable with pmc_gutenberg_story_block_config, and can contain
		// multiple post types.
		$this->story_block_config = [
			'post' => [
				'postType'     => 'post',
				'taxonomySlug' => 'category',
				'viewMoreText' => __( 'Read the story', 'pmc-gutenberg' ),
			],
		];

		$this->_styles = [
			[
				'name'       => 'horizontal',
				'label'      => __( 'Horizontal', 'pmc-gutenberg' ),
				'is_default' => true,
			],
			[
				'name'  => 'horizontal-border',
				'label' => __( 'Horizontal with border', 'pmc-gutenberg' ),
			],
			[
				'name'  => 'vertical',
				'label' => __( 'Vertical', 'pmc-gutenberg' ),
			],
			[
				'name'  => 'vertical-border',
				'label' => __( 'Vertical with border', 'pmc-gutenberg' ),
			],
		];

		$this->create_story_block( 'story' );
	}

	/**
	 * Inject additional block-specific data.
	 *
	 * @codeCoverageIgnore Method will move to PMC\Larva shortly.
	 *
	 * @param string $block_content Block's rendered content
	 * @param array  $block         Block data.
	 * @return array
	 */
	public function larva_data( string $block_content, array $block ): array {
		$data = parent::larva_data( $block_content, $block );

		if ( $this->_attributes['hasDisplayedByline'] ) {
			(
				new Components\C_Tagline_Author(
					[
						'post_id' => $this->_attributes['postID'],
					]
				)
			)->add_data( $data['c_tagline_author'] );
		} else {
			$data['c_tagline_author'] = false;
		}

		if ( ! empty( $data['c_timestamp'] ) ) {
				(
					new Components\C_Timestamp(
						[
							'post_id' => $this->_attributes['postID'],
						]
					)
				)->add_data( $data['c_timestamp'] );
		}

		return $data;
	}

	/**
	 * Determine Larva module and variant to use.
	 *
	 * Horizontal, left, without-borders corresponds to the prototype.
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	protected function _get_larva_module_with_variant( array $attrs ): string {
		$alignment = 'left' !== $attrs['alignment'] ? $attrs['alignment'] : '';
		$style     = $attrs['className'] ?? '';
		$direction = false !== strpos( $style, 'vertical' )
			? 'vertical'
			: '';
		$borders   = false !== strpos( $style, 'border' )
			? 'borders'
			: '';

		$variants = array_filter(
			[
				$alignment,
				$direction,
				$borders,
			]
		);

		if ( empty( $variants ) ) {
			$variants[] = 'prototype';
		}

		return sprintf(
			'%1$s.%2$s',
			$this->larva_module,
			implode(
				'-',
				$variants
			)
		);
	}
}
