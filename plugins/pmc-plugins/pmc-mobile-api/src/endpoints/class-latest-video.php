<?php
/**
 * This file contains the Endpoints\Latest_Video class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Objects\Video_Object;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Has_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Section_Links;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Video_Card;
use WP_REST_Request;
use WP_Term;
use WP_Query;

/**
 * Latest_Video endpoint class.
 */
class Latest_Video extends Public_Endpoint implements Has_Definitions, Has_Pagination {

	use Usable_Definitions;

	/**
	 * Video taxonomy used for this endpoint.
	 *
	 * @var string
	 */
	protected $taxonomy = 'category';

	/**
	 * Video post type used for this endpoint.
	 *
	 * @var string
	 */
	protected $post_type = 'post';

	/**
	 * Get the request params for the endpoint.
	 *
	 * @return array
	 */
	public function get_request_params(): array {
		return [
			'category' => [
				'description' => __( 'The video category to list.', 'pmc-mobile-api' ),
				'type'        => 'string',
				'required'    => false,
			],
		];
	}

	/**
	 * Get mobile section links.
	 *
	 * @return array
	 */
	protected function get_section_links(): array {
		return ( new Menu() )->get_section_links();
	}

	/**
	 * Get title.
	 *
	 * @param WP_REST_Request $request REST request data.
	 * @return string
	 */
	protected function get_title( WP_REST_Request $request ): string {
		$term = $this->get_term( $request );

		if ( $term instanceof WP_Term ) {
			return $term->name;
		}

		return __( 'Latest Videos', 'pmc-mobile-api' );
	}

	/**
	 * Get video items.
	 *
	 * @param WP_REST_Request $request REST request data.
	 * @return array
	 */
	protected function get_items( WP_REST_Request $request ): array {
		$query_args = [
			'post_type'      => $this->post_type,
			'fields'         => 'ids',
			'order'          => 'DESC',
			'post_status'    => 'publish',
			'paged'          => $request['page'],
			'posts_per_page' => $request['per_page'],
		];

		$term = $this->get_term( $request );

		if ( $term instanceof WP_Term ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$query_args['tax_query'] = [
				[
					'taxonomy'         => $this->taxonomy,
					'field'            => 'term_id',
					'terms'            => [ $term->term_id ],
					'include_children' => false,
				],
			];
		}

		// Get items.
		$query = new WP_Query( $query_args );

		if ( empty( $query->posts ) && ! is_array( $query->posts ) ) {
			return [ 'items' => [] ];
		}

		// Set the total number of items, for pagination.
		$this->total_items = $query->found_posts;

		return array_map( [ $this, 'map_video_object' ], (array) $query->posts );
	}

	/**
	 * Map video object.
	 *
	 * @param int $post_id Video post ID.
	 * @return array
	 */
	protected function map_video_object( int $post_id ): array {
		return ( new Video_Object( \get_post( $post_id ) ) )->get_video();
	}

	/**
	 * Get category term from request.
	 *
	 * @param WP_REST_Request $request REST request data.
	 * @return WP_Term|null
	 */
	protected function get_term( WP_REST_Request $request ): ?WP_Term {
		$category = $request['category'] ?? '';

		if ( empty( $category ) ) {
			return null;
		}

		// Get term object.
		$term = \get_term_by( 'slug', $category, $this->taxonomy );

		if ( ! $term instanceof WP_Term ) {
			return null;
		}

		return $term;
	}

	/**
	 * Retrieves the route schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_response_schema(): array {
		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Mobile App Latest Video', 'pmc-mobile-api' ),
			'type'       => 'object',
			'properties' => [
				'title'         => [
					'type' => 'string',
				],
				'items'         => [
					'type'  => 'array',
					'items' => $this->add_definition( new Video_Card() ),
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
