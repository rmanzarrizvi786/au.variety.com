<?php
/**
 * This file contains the Endpoints\Home class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Objects\Ad_Object;
use PMC\Mobile_API\Endpoints\Objects\Article_Object;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Has_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Landing_Page_Module;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Section_Links;
use WP_Post;

/**
 * Home endpoint class.
 */
class Home extends Public_Endpoint implements Has_Definitions, Has_Pagination {

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
	 * Get the ad.
	 *
	 * @param array $data Ad sizes.
	 * @return array
	 */
	public function get_ad( $data = [] ): array {
		return [
			'layout'  => 'advertisement',
			'ad-size' => ( new Ad_Object( $data ) )->get_ad(),
		];
	}

	/**
	 * Retrieves the route schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_response_schema(): array {
		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Mobile App Home', 'pmc-mobile-api' ),
			'type'       => 'object',
			'properties' => [
				'modules'       => [
					'type'  => 'array',
					'items' => $this->add_definition( new Landing_Page_Module() ),
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
