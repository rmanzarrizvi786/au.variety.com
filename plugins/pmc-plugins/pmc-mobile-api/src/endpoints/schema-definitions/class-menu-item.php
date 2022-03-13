<?php
/**
 * This file contains the Endpoints\Schema_Definitions\Menu_Item class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

/**
 * Menu_Item schema.
 */
class Menu_Item implements Definition {

	/**
	 * Get the schema slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'menu-item';
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
				'id'                => [
					'type' => 'int',
				],
				'title'             => [
					'type' => 'string',
				],
				'taxonomy'          => [
					'type' => 'string',
				],
				'use-parent'        => [
					'type' => 'bool',
				],
				'custom-parent-id'  => [
					'type' => 'int',
				],
				'custom-parent-tax' => [
					'type' => 'string',
				],
				'link'              => [
					'type'   => 'string',
					'format' => 'uri',
				],
				'children'          => [
					'type'  => 'array',
					'items' => [
						'$ref' => "#/definitions/{$this->get_slug()}",
					],
				],
			],
		];
	}
}
