<?php
/**
 * Interface for classes extending `\PMC\Gutenberg\Block_Base` and rendering the
 * block contents via a Larva pattern.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\Interfaces\Block_Base;

/**
 * Interface With_Larva_Data.
 */
interface With_Larva_Data {
	/**
	 * Populate Larva data.
	 *
	 * @param string $block_content Block's inner content, generally empty.
	 * @param array  $block         Block details, including attributes.
	 * @return array|null
	 */
	public function larva_data( string $block_content, array $block ): ?array;
}
