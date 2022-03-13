<?php
/**
 * This file contains the Endpoints\Section_Front class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Objects\Article_Object;
use PMC\Mobile_API\Endpoints\Objects\Term_Object;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Has_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Post_Card;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Section_Links;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Term;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;
use WP_Error;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;
use WP_Taxonomy;
use WP_Term;

/**
 * Section_Front endpoint class.
 */
class Section_Front extends Public_Endpoint implements Has_Definitions, Has_Pagination {

	use Usable_Definitions;

	/**
	 * Taxonomy slug for this endpoint.
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * Term.
	 *
	 * @var WP_Term
	 */
	protected $term;

	/**
	 * Term model.
	 *
	 * @var Term_Object
	 */
	protected $term_object;

	/**
	 * Hero Ids.
	 *
	 * @var array
	 */
	public $hero_ids;

	/**
	 * Section_Front constructor.
	 *
	 * @param string $taxonomy Taxonomies which to register as section fronts.
	 */
	public function __construct( string $taxonomy ) {
		$this->taxonomy = $taxonomy;
	}

	/**
	 * Retrieves the route schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_response_schema(): array {
		$title = sprintf(
			/* translators: %s: taxonomy name */
			__( 'Mobile App Section Front for %s', 'pmc-mobile-api' ),
			$this->get_taxonomy_label()
		);

		$card_schema          = $this->add_definition( new Post_Card() );
		$section_links_schema = $this->add_definition( new Section_Links() );

		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $title,
			'type'       => 'object',
			'properties' => [
				'term'             => $this->add_definition( new Term() ),
				'subsection-links' => $section_links_schema,
				'hero'             => [
					'type'  => 'array',
					'items' => $card_schema,
				],
				'river'            => [
					'type'       => 'object',
					'properties' => [
						'title' => [
							'type' => 'string',
						],
						'items' => [
							'type'  => 'array',
							'items' => $card_schema,
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

	/**
	 * Get the request params for the endpoint.
	 *
	 * @return array
	 */
	public function get_request_params(): array {
		return [
			'id' => [
				'description' => __( 'Section term ID.', 'pmc-mobile-api' ),
				'type'        => 'integer',
				'required'    => true,
			],
		];
	}

	/**
	 * Send the API response for the REST endpoint.
	 *
	 * @param WP_REST_Request $request REST request data.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function rest_response( WP_REST_Request $request ) {

		// Get endpoint term.
		$taxonomy = $this->get_taxonomy();
		$term     = \get_term_by( 'id', $request['id'], $taxonomy );

		if ( ! $term instanceof WP_Term ) {
			return new WP_Error(
				'rest_term_invalid_id',
				__( 'Invalid term ID.', 'pmc-mobile-api' ),
				[
					'status' => 404,
				]
			);
		}

		$this->term        = $term;
		$this->term_object = new Term_Object( $term );

		return parent::rest_response( $request );
	}

	/**
	 * Get mobile section links.
	 *
	 * @return array
	 */
	protected function get_section_links() {
		return ( new Menu() )->get_section_links();
	}

	/**
	 * Get mobile subsection links.
	 *
	 * @return array
	 */
	protected function get_subsection_links() {
		return [];
	}

	/**
	 * Get hero.
	 *
	 * @return array
	 */
	protected function get_hero() {
		return [
			'items' => [],
		];
	}

	/**
	 * Get river.
	 *
	 * @param WP_REST_Request $request REST request data.
	 * @return array
	 */
	protected function get_river( $request ): array {

		// Initialize.
		$river_data = [
			'title' => __( 'Latest', 'pmc-mobile-api' ),
			'items' => [],
		];

		$main_tax_query = [
			'taxonomy'         => $this->taxonomy,
			'field'            => 'term_id',
			'terms'            => [ $this->term->term_id ],
			'include_children' => false,
		];

		if ( isset( $request['add_taxonomy'] ) && isset( $request['add_taxonomy_id'] ) ) {
			$tax_query = [
				'relation' => 'AND',
				[
					'taxonomy'         => $request['add_taxonomy'],
					'field'            => 'term_id',
					'terms'            => [ $request['add_taxonomy_id'] ],
					'include_children' => false,
				],
				$main_tax_query,
			];
		} else {
			$tax_query = [
				$main_tax_query,
			];
		}

		$date_limit = explode( '-', gmdate( 'Y-m-d', strtotime( '6 months ago' ) ) );

		// Get the posts.
		$query = new WP_Query(
			[
				'post_type'      => [ 'any' ],
				'fields'         => 'ids',
				'order'          => 'DESC',
				'post_status'    => 'publish',
				'paged'          => $request['page'],
				'posts_per_page' => $request['per_page'],
				'date_query'     => [
					'after' => [
						'year'  => $date_limit[0],
						'month' => $date_limit[1],
						'day'   => $date_limit[2],
					],
				],
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				'tax_query'      => $tax_query,
			]
		);

		// Assign total posts for pagination.
		$this->total_items = $query->found_posts;

		$post_ids_clean = [];

		foreach ( $query->posts as $post_id ) {
			if ( in_array( $post_id, (array) $this->hero_ids, true ) ) {
				continue;
			}

			$post_ids_clean[] = $post_id;
		}

		// Map the IDs to cards.
		$river_data['items'] = array_map(
			function( $post_id ) {
				return ( new Article_Object( \get_post( $post_id ) ) )->get_post_card();
			},
			$post_ids_clean ?? []
		);

		return $river_data;
	}

	/**
	 * Get term data.
	 *
	 * @return array
	 */
	public function get_term() {
		return $this->term_object->get_term();
	}

	/**
	 * Get the taxonomy label (plural).
	 *
	 * @return string
	 */
	protected function get_taxonomy_label(): string {
		$tax_object = get_taxonomy( $this->get_taxonomy() );
		if ( $tax_object instanceof WP_Taxonomy ) {
			return $tax_object->labels->name ?? $tax_object->label ?? '';
		}

		return '';
	}

	/**
	 * Get this section front's taxonomy.
	 *
	 * @return string
	 */
	protected function get_taxonomy(): string {
		return $this->taxonomy;
	}
}
