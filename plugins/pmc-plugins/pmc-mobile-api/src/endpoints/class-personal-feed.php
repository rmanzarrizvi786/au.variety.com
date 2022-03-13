<?php
/**
 * This file contains the PMC\Mobile_API\Endpoints\Personal_Feed class
 *
 * @package WWD_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Objects\Article_Object;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Has_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Post_Card;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;
use WP_REST_Request;

/**
 * Runway_Shows endpoint class.
 */
class Personal_Feed extends Public_Endpoint implements Has_Definitions, Has_Pagination {

	use Usable_Definitions;

	/**
	 * Get the items.
	 *
	 * Query designers (collections), filtered as having had a
	 * show (galleries or reviews) in that season and city.
	 *
	 * @param WP_REST_Request $request REST request data.
	 *
	 * @return array Array of collections.
	 */
	protected function get_items( WP_REST_Request $request ) {

		$personalization_query    = apply_filters( 'mobile_api_personalization_query', 'category' );
		$personalization_taxonomy = apply_filters( 'mobile_api_personalization_taxonomy', 'category' );

		$tax_query = [];

		$params = wp_parse_args(
			[
				$personalization_query => $request->get_param( $personalization_query ),
				'per_page'             => $request->get_param( 'per_page' ),
				'page'                 => $request->get_param( 'page' ),
			],
			$request->get_default_params()
		);


		$vertical_ids = explode( ',', $params[ $personalization_query ] );

		// Set up the tax query with verticals.
		if ( ! empty( $params[ $personalization_query ] ) ) {
			$tax_query = [
				[
					'taxonomy' => $personalization_taxonomy,
					'field'    => 'term_id',
					'terms'    => $vertical_ids,
				],
			];
		}

		// Query galleries and reviews with the season/city.
		$query = new \WP_Query(
			[
				'post_type'      => [ 'any' ],
				'fields'         => 'ids',
				'order'          => 'DESC',
				'post_status'    => 'publish',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				'tax_query'      => $tax_query,
				// phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				'posts_per_page' => absint( $params['per_page'] ),
				'paged'          => absint( $params['page'] ),
			]
		);

		// Assign total posts for pagination.
		$this->total_items = $query->found_posts;

		// Map the IDs to cards.
		$river_data = array_map(
			function( $post_id ) {
				return ( new Article_Object( \get_post( $post_id ) ) )->get_post_card();
			},
			$query->posts ?? []
		);

		return $river_data;
	}

	/**
	 * Retrieves the route schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_response_schema(): array {
		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Mobile App Personal Feed', 'pmc-mobile-api' ),
			'type'       => 'object',
			'properties' => [
				'items' => $this->add_definition( new Post_Card() ),
			],
		];

		$definitions = $this->get_definitions();
		if ( ! empty( $definitions ) ) {
			$schema['definitions'] = $definitions;
		}

		return $schema;
	}

	/**
	 * Get the request params for the endpoint.
	 *
	 * @return array
	 */
	public function get_request_params(): array {
		$personalization_query = apply_filters( 'mobile_api_personalization_query', 'category' );

		return [
			$personalization_query => [
				'description' => __( 'Taxonomy IDs with which to filter results.', 'pmc-mobile-api' ),
				'type'        => 'string',
				'required'    => true,
			],
		];
	}
}
