<?php

namespace PMC\Gutenberg\Blocks;

use PMC_Ads;
use PMC\Gutenberg\Block_Base;
use PMC\Gutenberg\Interfaces\Block_Base\With_Dependencies;
use PMC\Gutenberg\Interfaces\Block_Base\With_Render_Callback;
use WP_Block;

class Ad extends Block_Base implements With_Dependencies, With_Render_Callback {

	public function __construct() {
		$this->_block          = 'ad';
		$this->_has_stylesheet = true;
	}

	/**
	 * Render ad.
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

		if ( empty( $attrs['provider'] ) || empty( $attrs['location'] ) ) {
			return '';
		}

		return pmc_adm_render_ads(
			$attrs['location'],
			'',
			false,
			$attrs['provider']
		);
	}

	/**
	 * Specify classes that must be loaded for this block to be available.
	 *
	 * @return array
	 */
	public function get_dependent_classes(): array {
		return [
			PMC_Ads::class,
		];
	}
}
