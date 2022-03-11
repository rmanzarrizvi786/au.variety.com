<?php
/**
 * This file contains the PMC\VY\Mobile_API\Endpoints\VY_Video_Playlists class
 *
 * @package VY_Mobile_API
 */

namespace PMC\VY\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Objects\Term_Object;
use PMC\Mobile_API\Endpoints\Public_Endpoint;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Term;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;

/**
 * Video Landing endpoint class.
 */
class VY_Video_Playlists extends Public_Endpoint {


	use Usable_Definitions;

	/**
	 * Personalization constructor.
	 */
	public function __construct() {

		add_action( 'fm_term_vcategory', [ $this, 'add_term_meta' ] );
	}

	/**
	 * Get the term items.
	 *
	 * @return array
	 */
	public function get_items(): array {

		$terms = get_terms(
			[
				'taxonomy'   => 'vcategory',
				'hide_empty' => true,
				'orderby'    => 'meta_value_num', // orderby "meta_value_num" orders by first key in meta_query
				'meta_query' => [ // phpcs:ignore slow query ok.
					[
						'key'     => 'mobile_api_order',
						'compare' => 'EXISTS',
					],
					[
						'relation' => 'AND',
						[
							'key'     => 'featured_image',
							'compare' => 'EXISTS',
						],
						[
							'key'     => 'featured_image',
							'compare' => '!=',
							'value'   => '',
						],
					],
				],
			]
		);

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return [];
		}

		$terms = array_map(
			function( \WP_Term $term ) {
				return ( new Term_Object( $term ) )->get_term();
			},
			(array) $terms
		);

		return array_values( $terms );
	}

	/**
	 * Retrieves the route schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_response_schema(): array {
		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Mobile App Personalization', 'pmc-variety' ),
			'type'       => 'array',
			'properties' => [
				'items' => [
					'type'  => 'object',
					'items' => $this->add_definition( new Term() ),
				],
			],
		];

		return $schema;
	}

	/**
	 * Add Fieldmanager Term meta that will control if term should be shown as child in the archive pages.
	 * @return void
	 */
	public function add_term_meta(): void {

		$fm_media = new \Fieldmanager_Media(
			[
				'name'         => 'featured_image',
				'button_label' => __( 'Upload file', 'pmc-variety' ),
			]
		);

		$fm_media->add_term_meta_box( 'Featured Image', 'vcategory' );

		$fm = new \Fieldmanager_TextField(
			[
				'name'          => 'mobile_api_order',
				'input_type'    => 'number',
				'default_value' => 0,
			]
		);

		$fm->add_term_meta_box( 'Mobile API Order', 'vcategory' );
	}
}
