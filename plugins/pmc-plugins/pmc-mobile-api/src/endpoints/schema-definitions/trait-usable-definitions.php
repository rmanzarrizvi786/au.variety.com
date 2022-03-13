<?php
/**
 * This file contains the Use_Definitions trait
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

use PMC\Mobile_API\Route_Registrar;

/**
 * Reusable implementation of the Has_Definitions interface.
 */
trait Usable_Definitions {

	/**
	 * Definitions.
	 *
	 * @var array
	 */
	protected $definitions = [];

	/**
	 * Register a definition for the current endpoint.
	 *
	 * @param Definition $schema Schema object.
	 * @return array Reference for the definition, `['$ref' => '...']`.
	 */
	public function add_definition( Definition $schema ): array {
		$this->definitions[ $schema->get_slug() ] = $schema->get_schema();
		if ( $schema instanceof Has_Definitions ) {
			$this->definitions = array_merge(
				$schema->get_definitions(),
				$this->definitions
			);
		}
		return [
			'$ref' => "#/definitions/{$schema->get_slug()}",
		];
	}

	/**
	 * Get the definitions for the current endpoint.
	 *
	 * @return array
	 */
	public function get_definitions(): array {
		return $this->definitions;
	}
}
