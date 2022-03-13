<?php
/**
 * This file contains the PMC\Mobile_API\Endpoints\Schema_Definitions\Config class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

/**
 * Config schema.
 */
class Config implements Definition {

	/**
	 * Get the schema slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'config';
	}

	/**
	 * Get the item schema.
	 *
	 * @return array
	 */
	public function get_schema(): array {
		return [
			'type'       => 'object',
			'properties' => [
				'latestAppBuildNumber' => [
					'type' => 'string',
				],
				'minBuildNumber'       => [
					'type' => 'string',
				],
				'playstoreLink'        => [
					'type'   => 'string',
					'format' => 'uri',
				],
			],
		];
	}
}
