<?php
/**
 * This file contains the Endpoints\Schema_Definitions\Ad class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

/**
 * Ad schema.
 */
class Ad implements Definition {

	/**
	 * Get the schema slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'ad';
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
				'width'  => [
					'type'        => 'integer',
					'description' => __( 'Width of the advertisement.', 'pmc-mobile-api' ),
				],
				'height' => [
					'type'        => 'integer',
					'description' => __( 'Height of the advertisement.', 'pmc-mobile-api' ),
				],
			],
		];
	}
}
