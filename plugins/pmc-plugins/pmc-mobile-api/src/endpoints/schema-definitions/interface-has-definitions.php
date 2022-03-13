<?php
/**
 * This file contains the Has_Definitions Interface
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

/**
 * Interface for endpoints that have definitions.
 */
interface Has_Definitions {

	/**
	 * Register a definition for the current endpoint.
	 *
	 * @param Definition $schema Schema object.
	 * @return array Reference for the definition, `['$ref' => '...']`.
	 */
	public function add_definition( Definition $schema ): array;

	/**
	 * Get the definitions for the current endpoint.
	 *
	 * @return array
	 */
	public function get_definitions(): array;
}
