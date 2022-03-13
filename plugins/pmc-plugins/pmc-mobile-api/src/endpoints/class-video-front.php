<?php
/**
 * This file contains the Endpoints\Video_Front class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Has_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Section_Links;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Video_Card;

/**
 * Video_Front endpoint class.
 */
class Video_Front extends Public_Endpoint implements Has_Definitions {

	use Usable_Definitions;

	/**
	 * Get mobile section links.
	 *
	 * @return array
	 */
	protected function get_section_links(): array {
		return ( new Menu() )->get_section_links();
	}

	/**
	 * Retrieves the route schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_response_schema(): array {
		$video_def = $this->add_definition( new Video_Card() );

		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Mobile App Video Section Front', 'pmc-mobile-api' ),
			'type'       => 'object',
			'properties' => [
				'hero'          => $video_def,
				'modules'       => [
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
							'items' => [
								'type'  => 'array',
								'items' => $video_def,
							],
						],
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
