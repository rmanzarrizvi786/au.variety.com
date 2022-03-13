<?php
/**
 * Render ad block with Digital Daily story.
 */

namespace PMC\Gutenberg\Blocks;

use PMC\Digital_Daily\Full_View;
use PMC\Gutenberg\Block_Base;
use PMC\Gutenberg\Interfaces\Block_Base\With_Render_Callback;
use WP_Block;

/**
 * Class Digital_Daily_Polar_Ad.
 */
class Digital_Daily_Polar_Ad extends Block_Base implements With_Render_Callback {

	public function __construct() {
		$this->_block = 'digital-daily-polar-ad';
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

		$inner_block_index = Full_View::is() ? 1 : 0;

		if ( empty( $block->parsed_block['innerBlocks'][ $inner_block_index ] ) ) {
			return '';
		}

		return render_block( $block->parsed_block['innerBlocks'][ $inner_block_index ] );

	}

}
