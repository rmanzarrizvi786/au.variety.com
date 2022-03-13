<?php
/**
 * This file contains the PMC\Mobile_API\Endpoints\Config class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Schema_Definitions\Config as Config_Schema_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Has_Definitions;

/**
 * Config endpoint class.
 */
class Config extends Public_Endpoint implements Has_Definitions {

	use Usable_Definitions;

	/**
	 * Get app update config.
	 *
	 * @return array
	 */
	public function get_appUpdateConfig(): array {
		return [
			'iOS'     => [
				'latestAppBuildNumber' => '4.0',
				'minBuildNumber'       => '4.0',
				'playstoreLink'        => '',
			],
			'Android' => [
				'latestAppBuildNumber' => '1.0',
				'minBuildNumber'       => '1.0',
				'playstoreLink'        => '',
			],
		];
	}

	/**
	 * Retrieves the route schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_response_schema(): array {

		$config_schema = $this->add_definition( new Config_Schema_Definitions() );

		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Mobile App Config', 'pmc-mobile-api' ),
			'type'       => 'object',
			'properties' => [
				'appUpdateConfig' => [
					'type'       => 'object',
					'properties' => [
						'iOS'     => $config_schema,
						'Android' => $config_schema,
					],
				],
			],
		];

		$definitions = $this->get_definitions();
		if ( ! empty( $definitions ) ) {
			$schema['definitions'] = $definitions;
		}

		return $schema;
	}
}
