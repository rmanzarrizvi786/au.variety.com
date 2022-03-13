<?php
/**
 * This file contains the Endpoints\Schema_Definitions\Landing_Page_Module class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

/**
 * Landing Page Module Schema.
 */
class Landing_Page_Module implements Definition, Has_Definitions {

	use Usable_Definitions;

	/**
	 * Get the schema slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'landing-page-module';
	}

	/**
	 * Get the item schema.
	 *
	 * @return array
	 */
	public function get_schema(): array {

		/**
		 * Filters the available landing page module layouts.
		 *
		 * @param array $layouts Layout options.
		 */
		$layouts = apply_filters(
			'pmc_mobile_api_landing_page_module_layouts',
			[
				'advertisement',
				'attend',
				'hero',
				'latest-news',
				'latest-videos',
				'section',
				'section-excerpt',
				'sponsored-card',
				'trending',
			]
		);
		sort( $layouts );

		return [
			'description' => __( 'A module/block of posts to render on the landing page.', 'pmc-mobile-api' ),
			'type'        => 'object',
			'properties'  => [
				'title'   => [
					'type'        => 'string',
					'description' => __( 'Module title.', 'pmc-mobile-api' ),
				],
				'link'    => [
					'type'        => 'string',
					'format'      => 'uri',
					'description' => __( "Link to the module's landing page.", 'pmc-mobile-api' ),
				],
				'layout'  => [
					'type'        => 'string',
					'description' => __( 'Layout is an indicator of how the content should be rendered in the list of modules.', 'pmc-mobile-api' ),
					'enum'        => $layouts,
				],
				'items'   => [
					'type'  => 'array',
					'items' => $this->add_definition( new Post_Card() ),
				],
				'ad-size' => $this->add_definition( new Ad() ),
			],
		];
	}
}
