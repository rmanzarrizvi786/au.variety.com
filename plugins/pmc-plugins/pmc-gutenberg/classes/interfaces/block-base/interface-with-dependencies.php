<?php
/**
 * Interface for classes extending `\PMC\Gutenberg\Block_Base` and that should
 * only be available when specific dependencies are satisfied.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\Interfaces\Block_Base;

/**
 * Interface With_Dependencies.
 */
interface With_Dependencies {
	/**
	 * Specify classes that must be loaded for this block to be available.
	 *
	 * @return array
	 */
	public function get_dependent_classes(): array;
}
