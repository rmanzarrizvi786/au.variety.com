<?php
/**
 * This file contains the Mobile Push API class.
 *
 * @package PMC_Push_Notifications
 */

namespace PMC\Push_Notifications;

/**
 * Mobile_Push_API Class.
 */
class Mobile_Push_API {

	/**
	 * API URL.
	 *
	 * Example: https://YOUR_SUBDOMAIN.rest.marketingcloudapis.com
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * Rest Api key.
	 *
	 * @var string
	 */
	private $rest_api_key;

	/**
	 * App key.
	 *
	 * @var string
	 */
	private $app_id;

	/**
	 * Initialize things.
	 *
	 * @param array $params Params for the request.
	 */
	public function __construct( array $params = [] ) {

		// Bail early.
		if ( empty( $params ) ) {
			return;
		}

		$this->api_url      = 'https://onesignal.com/api/v1/';
		$this->rest_api_key = $params['rest_api_key'] ?? '';
		$this->app_id       = $params['app_id'] ?? '';
	}

	/**
	 * Create push message.
	 *
	 * @link https://documentation.onesignal.com/reference/create-notification
	 *
	 * @param array $params Params to create the push message.
	 * @return mixed
	 */
	public function create_push_message( array $params ) {
		return $this->post( 'notifications', $params );
	}

	/**
	 * Delete an existing push message.
	 *
	 * @link https://documentation.onesignal.com/reference/cancel-notification
	 *
	 * @param string $message_id ID of the push message to delete.
	 * @return mixed
	 */
	public function delete_push_message( string $message_id ) {
		return $this->delete(
			sprintf( 'notifications/%1$s?app_id=%2$s', $message_id, $this->app_id )
		);
	}

	/**
	 * Request resource.
	 *
	 * @param  string $method   Request method.
	 * @param  string $resource Resource.
	 * @param  array  $data     Request data.
	 * @return object Request response.
	 */
	private function request( string $method, string $resource, array $data = [] ) {
		$response = $this->do_request(
			$this->api_url . ltrim( $resource, '/' ),
			$method,
			$data
		);

		return $this->get_request_response( $response );
	}

	/**
	 * Do the actual request.
	 *
	 * @param  string $url      URL.
	 * @param  string $method   Request method.
	 * @param  array  $data     Request data.
	 * @return \WP_Error|array   Response.
	 */
	private function do_request( string $url, string $method, array $data = [] ) {
		$params = [
			'method'  => $method,
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . $this->rest_api_key,
			],
		];

		if ( ! empty( $data ) ) {
			if ( in_array( $method, [ 'GET', 'DELETE' ], true ) ) {
				$url = \add_query_arg( $data, $url );
			} else {
				$params['body'] = \wp_json_encode( $data );
			}
		}

		// Do request.
		return wp_safe_remote_request( $url, $params );
	}

	/**
	 * Get response of a request.
	 *
	 * @param array $response Response object.
	 * @return object|bool
	 */
	private function get_request_response( array $response ) {

		// Decode response body.
		$code = (int) wp_remote_retrieve_response_code( $response );

		// Get response body.
		$body = wp_remote_retrieve_body( $response );

		if ( $code < 300 && empty( $body ) ) {
			$body = 'true';
		}

		return json_decode( $body );
	}

	/**
	 * GET resource.
	 *
	 * @param string $resource Resource data.
	 * @param array  $args     Request args.
	 * @return object
	 */
	public function get( string $resource, array $args = [] ) {
		return $this->request( 'GET', $resource, $args );
	}

	/**
	 * POST resource.
	 *
	 * @param string $resource Resource data.
	 * @param array  $args     Request args.
	 * @return object
	 */
	public function post( string $resource, array $args = [] ) {
		return $this->request( 'POST', $resource, $args );
	}

	/**
	 * Update/PUT resource.
	 *
	 * @param string $resource Resource data.
	 * @param array  $args     Request args.
	 * @return object
	 */
	public function put( string $resource, array $args = [] ) {
		return $this->request( 'PUT', $resource, $args );
	}

	/**
	 * PATCH/UPDATE resource.
	 *
	 * @param string $resource Resource data.
	 * @param array  $args     Request args.
	 * @return object
	 */
	public function patch( string $resource, array $args = [] ) {
		return $this->request( 'PATCH', $resource, $args );
	}

	/**
	 * DELETE resource.
	 *
	 * @param string $resource Resource data.
	 * @return object
	 */
	public function delete( $resource ) {
		return $this->request( 'DELETE', $resource );
	}
}
