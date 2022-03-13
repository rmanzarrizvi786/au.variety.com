<?php
/**
 * This file contains the Endpoints\Schema_Definitions\Section_Links class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

/**
 * Section_Links schema.
 */
class Section_Links implements Definition {

	/**
	 * Get the schema slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'section-links';
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
				'type'       => 'object',
				'properties' => [
					'title' => [
						'type' => 'string',
					],
					'link'  => [
						'type'   => 'string',
						'format' => 'uri',
					],
				],
			],
		];
	}
}
