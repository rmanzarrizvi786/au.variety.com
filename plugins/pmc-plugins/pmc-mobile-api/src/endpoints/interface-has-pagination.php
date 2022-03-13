<?php
/**
 * This file contains the Has_Pagination Interface
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use WP_REST_Response;

/**
 * Interface for endpoints that have pagination.
 */
interface Has_Pagination {

	/**
	 * Set headers to let the Client Script be aware of the pagination.
	 *
	 * @param  WP_REST_Response $response The response data.
	 * @param  integer          $total    The total number of found items.
	 * @param  integer          $per_page The number of items per page of results.
	 * @return WP_REST_Response $response The response data.
	 */
	public function rest_response_add_total_headers( WP_REST_Response $response, $total = 0, $per_page = 0 );

}
