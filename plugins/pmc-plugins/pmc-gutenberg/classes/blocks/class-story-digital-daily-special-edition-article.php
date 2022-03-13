<?php
/**
 * Story block for Digital Daily Special Edition articles.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\Blocks;

use PMC\Digital_Daily\CPT;
use const PMC\Digital_Daily\POST_TYPE_SPECIAL_EDITION_ARTICLE;
use PMC\Gutenberg\Story_Block_Engine;
use PMC\Gutenberg\Interfaces\Block_Base;
use PMC\Gutenberg\Traits\Block_Base\Digital_Daily_Common_Elements;
use function PMC\Larva\get_id_attribute_for_post_id;
use WP_Block;

/**
 * Class Story_Digital_Daily_Special_Edition_Article.
 */
class Story_Digital_Daily_Special_Edition_Article extends Story_Block_Engine implements Block_Base\With_Dependencies, Block_Base\With_Render_Callback {
	/**
	 * Reusable pattern elements shared across Digital Daily blocks.
	 */
	use Digital_Daily_Common_Elements;

	/**
	 * Story_Digital_Daily_Special_Edition_Article constructor.
	 */
	public function __construct() {
		$this->story_block_config = [
			POST_TYPE_SPECIAL_EDITION_ARTICLE => [
				'postType'     => POST_TYPE_SPECIAL_EDITION_ARTICLE,
				'taxonomySlug' => 'category',
				'viewMoreText' => '', // Full content appears in block output.
			],
		];

		$this->create_story_block(
			'story-digital-daily-special-edition-article'
		);
	}

	/**
	 * Render block.
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

		if ( ! $attrs['postID'] ) {
			return '';
		}

		$this->_attributes = [
			'postID' => $attrs['postID'],
		];

		ob_start();
		do_action( 'pmc_larva_do_the_content', $this->_attributes['postID'] );
		$article_content = ob_get_clean();

		$permalink_attr = '';
		$title_attr     = '';
		$this->_set_permalink_attr( $permalink_attr );
		$this->_set_title_attr( $title_attr );

		return sprintf(
			'<div class="block-wrapper %1$s" id="%2$s" data-permalink="%3$s" data-title="%4$s">%5$s</div>',
			$this->_block,
			get_id_attribute_for_post_id( $this->_attributes['postID'] ),
			$permalink_attr,
			$title_attr,
			$article_content
		);
	}

	/**
	 * Called by the `render_block` method of the main `Gutenberg` class.
	 *
	 * Method signature must match parent method.
	 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	 *
	 * @param string $block_content The block content about to be appended.
	 * @param array $block The full block, including name and attributes.
	 * @return array|null Data that can be used with `PMC::render_template` or
	 *                    null to prevent block from rendering.
	 */
	public function larva_data( string $block_content, array $block ): ?array {
		// phpcs:enable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$this->template = null;

		return null;
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
