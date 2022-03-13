<?php
/**
 * This file contains the Endpoints\Schema_Definitions\Entitlements class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

/**
 * Entitlements schema.
 */
class Entitlements implements Definition {

	/**
	 * Get the schema slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'entitlements';
	}

	/**
	 * Get the item schema.
	 *
	 * @return array
	 */
	public function get_schema(): array {
		return [
			'type'  => 'array',
			'items' => [
				'type' => 'string',
			],
		];
	}
}
