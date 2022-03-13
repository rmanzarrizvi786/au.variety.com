<?php
/**
 * This file contains the Endpoint interface
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use WP_REST_Request;

/**
 * Interface for endpoints.
 *
 * All endpoints should be able to perform these functions at a minimum.
 */
interface Endpoint {

	/**
	 * Get the arguments passed to register_rest_route().
	 *
	 * @return array
	 */
	public function get_route_args(): array;

	/**
	 * Send the API response for the REST endpoint.
	 *
	 * @param \WP_REST_Request $request REST request data.
	 * @return \WP_REST_Response|\WP_Error WP_REST_Response or WP_Error.
	 */
	public function rest_response( WP_REST_Request $request );

	/**
	 * Get the request params for the endpoint.
	 *
	 * @return array
	 */
	public function get_request_params(): array;

	/**
	 * Retrieves the route schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_response_schema(): array;
}
