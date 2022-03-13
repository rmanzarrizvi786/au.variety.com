<?php
/**
 * This file contains the Endpoints\Schema_Definitions\Gallery_Card class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

/**
 * Gallery_Card schema.
 */
class Gallery_Card implements Definition, Has_Definitions {

	use Usable_Definitions;

	/**
	 * Get the schema slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'gallery-card';
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
				'id'          => [
					'type' => 'integer',
				],
				'images'      => [
					'type'  => 'array',
					'items' => $this->add_definition( new Image() ),
				],
				'image-count' => [
					'type' => 'integer',
				],
				'link'        => [
					'type'   => 'string',
					'format' => 'uri',
				],
			],
		];
	}
}
