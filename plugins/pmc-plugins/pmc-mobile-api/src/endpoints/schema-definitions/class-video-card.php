<?php
/**
 * This file contains the Endpoints\Schema_Definitions\Video_Card class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

/**
 * Video_Card schema.
 */
class Video_Card implements Definition, Has_Definitions {

	use Usable_Definitions;

	/**
	 * Get the schema slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'video-card';
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
				'id'           => [
					'type' => 'integer',
				],
				'published-at' => [
					'type'        => 'string',
					'format'      => 'date-time',
					'description' => __( 'The date and time the item was last published, in ISO 8601.', 'pmc-mobile-api' ),
				],
				'image'        => $this->add_definition( new Image() ),
				'duration'     => [
					'type' => 'string',
				],
				'link'         => [
					'type'   => 'string',
					'format' => 'uri',
				],
			],
		];
	}
}
