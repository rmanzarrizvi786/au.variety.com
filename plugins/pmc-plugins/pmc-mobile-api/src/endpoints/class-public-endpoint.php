<?php
/**
 * This file contains the Public_Endpoint abstract class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Objects\Article_Object;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Abstract class for public endpoints.
 */
abstract class Public_Endpoint implements Endpoint {

	/**
	 * Total items for pagination.
	 *
	 * @var int
	 */
	public $total_items = 0;

	/**
	 * Get the arguments passed to register_rest_route().
	 *
	 * @return array
	 */
	public function get_route_args(): array {
		// Add page to the request params if this endpoint has pagination.
		$request_params = $this->get_request_params();
		if ( $this instanceof Has_Pagination ) {
			$request_params = $this->add_pagination( $request_params );
		}

		return [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'rest_response' ],
				'permission_callback' => '__return_true',
				'args'                => $request_params,
			],
			'schema' => [ $this, 'get_response_schema' ],
		];
	}

	/**
	 * Get the request params for the endpoint.
	 *
	 * @return array
	 */
	public function get_request_params(): array {
		return [];
	}

	/**
	 * Add a page param to the request args definitions.
	 *
	 * @param array $params REST route request arguments.
	 * @return array
	 */
	protected function add_pagination( array $params ): array {
		$params['page'] = [
			'description'       => __( 'Current page of the collection.', 'pmc-mobile-api' ),
			'type'              => 'integer',
			'required'          => false,
			'default'           => 1,
			'minimum'           => 1,
			'maximum'           => 100,
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		];

		$params['per_page'] = [
			'description'       => __( 'Maximum number of items to be returned in result set.', 'pmc-mobile-api' ),
			'type'              => 'integer',
			'required'          => false,
			'default'           => 10,
			'minimum'           => 1,
			'maximum'           => 100,
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		];

		return $params;
	}

	/**
	 * Set headers to let the Client Script be aware of the pagination.
	 *
	 * @param  WP_REST_Response $response The response data.
	 * @param  integer          $total    The total number of found items.
	 * @param  integer          $per_page The number of items per page of results.
	 * @return WP_REST_Response $response The response data.
	 */
	public function rest_response_add_total_headers( WP_REST_Response $response, $total = 0, $per_page = 0 ) {
		$total_items = (int) $total;
		$max_pages   = ceil( $total_items / (int) $per_page );

		$response->header( 'X-WP-Total', $total_items );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		return $response;
	}

	/**
	 * Send the API response for the REST endpoint.
	 *
	 * @param WP_REST_Request $request REST request data.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function rest_response( WP_REST_Request $request ) {

		$response = $this->get_endpoint_response_fields();

		// phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UnusedVariable
		foreach ( $response as $key => $value ) {
			$method_key  = str_replace( '-', '_', $key );
			$method_name = "get_{$method_key}";

			if ( method_exists( $this, $method_name ) ) {
				$response[ $key ] = $this->$method_name( $request );
			}
		}

		$response = rest_ensure_response( $response );

		if ( ! $this instanceof Has_Pagination ) {
			return $response;
		}

		return $this->rest_response_add_total_headers( $response, $this->total_items, $request['per_page'] );
	}

	/**
	 * Getting endpoint default response fields from the schema.
	 *
	 * @return array
	 */
	public function get_endpoint_response_fields(): array {
		return array_map(
			function() {
				return '';
			},
			array_flip(
				array_keys(
					$this->get_response_schema()['properties'] ?? []
				)
			)
		);
	}

	/**
	 * Clean post IDs, removing null or empty values.
	 *
	 * @param array $post_ids An array of posts IDs.
	 * @return array
	 */
	protected function clean_post_ids( $post_ids ): array {

		// Bail early.
		if ( empty( $post_ids ) || ! \is_array( $post_ids ) ) {
			return [];
		}

		return \array_map( 'absint', \array_filter( $post_ids ) );
	}

	/**
	 * Map post card.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public function map_post_card( $post_id ): ?array {
		$post = \get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			return null;
		}

		$article = new Article_Object( $post );

		return $article->get_post_card();
	}
}
