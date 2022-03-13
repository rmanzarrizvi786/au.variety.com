<?php
/**
 * This file contains the Endpoints\Latest_News class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Objects\Article_Object;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Has_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Post_Card;
use WP_REST_Request;
use WP_Query;

/**
 * Latest_News endpoint class.
 */
class Latest_News extends Public_Endpoint implements Has_Definitions, Has_Pagination {

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
	 * Get river.
	 *
	 * @param WP_REST_Request $request REST request data.
	 * @return array
	 */
	protected function get_river( WP_REST_Request $request ): array {
		$query = new WP_Query(
			[
				'post_type'      => [ 'post' ],
				'fields'         => 'ids',
				'order'          => 'DESC',
				'post_status'    => 'publish',
				'paged'          => $request['page'],
				'posts_per_page' => $request['per_page'],
			]
		);

		// Check if there are any posts.
		if ( empty( $query->posts ) && ! is_array( $query->posts ) ) {
			return [ 'items' => [] ];
		}

		// Assign total posts for pagination.
		$this->total_items = $query->found_posts;

		return [
			'items' => array_map(
				function( $post_id ) {
					return ( new Article_Object( \get_post( $post_id ) ) )->get_post_card();
				},
				(array) $query->posts
			),
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
			'title'      => __( 'Mobile App Latest News', 'pmc-mobile-api' ),
			'type'       => 'object',
			'properties' => [
				'river'         => [
					'type'       => 'object',
					'properties' => [
						'items' => [
							'type'  => 'array',
							'items' => $this->add_definition( new Post_Card() ),
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
