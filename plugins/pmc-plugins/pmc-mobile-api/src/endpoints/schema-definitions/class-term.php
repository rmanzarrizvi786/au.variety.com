<?php
/**
 * This file contains the Endpoints\Schema_Definitions\Term class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

/**
 * Term schema.
 */
class Term implements Definition {

	use Usable_Definitions;

	/**
	 * Get the schema slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'term';
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
				'id'       => [
					'type' => 'integer',
				],
				'slug'     => [
					'type' => 'string',
				],
				'name'     => [
					'type' => 'string',
				],
				'image'    => [
					'type'   => 'string',
					'format' => 'uri',
				],
				'taxonomy' => [
					'type' => 'string',
				],
				'link'     => [
					'type'   => 'string',
					'format' => 'uri',
				],
			],
		];
	}
}
