<?php
/**
 * This file contains the Endpoints\Schema_Definitions\Post_Card class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

use PMC\Mobile_API\Endpoints\Schema_Definitions\Image;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Entitlements;

/**
 * Post_Card schema.
 */
class Post_Card implements Definition, Has_Definitions {

	use Usable_Definitions;

	/**
	 * Get the schema slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'post-card';
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
				'post-title'   => [
					'type' => 'string',
				],
				'category'     => [
					'type' => 'string',
				],
				'image'        => $this->add_definition( new Image() ),
				'byline'       => [
					'type' => 'string',
				],
				'body-preview' => [
					'type' => 'string',
				],
				'published-at' => [
					'type'        => 'string',
					'format'      => 'date-time',
					'description' => __( 'The date and time the item was last published, in ISO 8601.', 'pmc-mobile-api' ),
				],
				'entitlements' => $this->add_definition( new Entitlements() ),
				'post-type'    => [
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
