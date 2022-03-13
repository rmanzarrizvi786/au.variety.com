<?php
/**
 * This file contains the Schema Interface
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

/**
 * Schema interface.
 */
interface Definition {

	/**
	 * Get the schema slug.
	 *
	 * @return string
	 */
	public function get_slug(): string;

	/**
	 * Get the item schema.
	 *
	 * @return array
	 */
	public function get_schema(): array;
}
