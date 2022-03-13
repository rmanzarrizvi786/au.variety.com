<?php
/**
 * Render slider using Groups block to contain each slide.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\Blocks;

use PMC\Gutenberg\Block_Base;
use PMC\Gutenberg\Interfaces\Block_Base\With_Render_Callback;
use WP_Block;
use WWD\Inc\Larva\Controllers\Modules\Group_Slider as Larva_Controller;

/**
 * Class Group_Slider.
 *
 * @codeCoverageIgnore Cannot be tested until pattern is moved from
 *                     `pmc-wwd-2021` into `pmc-larva`.
 */
class Group_Slider extends Block_Base implements With_Render_Callback {
	/**
	 * Set up block.
	 */
	public function __construct() {
		$this->_block = 'group-slider';
	}

	/**
	 * Render block.
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
		$slides = [];

		foreach ( $block->inner_blocks as $group_block ) {
			// Not possible as only Groups blocks are allowed, just being safe.
			if ( 'core/group' !== $group_block->name ) {
				continue;
			}

			$slide = '';

			foreach ( $group_block->inner_blocks as $element ) {
				$slide .= $element->render();
			}

			if ( empty( $slide ) ) {
				continue;
			}

			$slides[] = sprintf(
				'<div class="%1$s">%2$s</div>',
				'slide',
				$slide
			);
		}

		return Larva_Controller::get_instance()->init(
			[
				'data' => [
					'heading_text'    => $attrs['heading'] ?? '',
					'subheading_text' => $attrs['subheading'] ?? '',
					'slides'          => $slides,
					'show_brand_logo' => $attrs['showBrandLogo'] ?? true,
				],
			]
		)->render( false );
	}
}
