<?php
/**
 * Interface for classes extending `\PMC\Gutenberg\Block_Base` and rendering the
 * block contents directly, via a Larva template or otherwise.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\Interfaces\Block_Base;

use WP_Block;

/**
 * Interface With_Render_Callback.
 */
interface With_Render_Callback {
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
	): string;
}
