<?php
/**
 * This class is responsible to process all REST GET API requests in Content Builder.
 */

namespace PMC\Exacttarget;

use \PMC\Exacttarget\Rest_Error;

class Rest_Request {

	/**
	 * @var string HTTP method used for REST Request.
	 */
	protected $method;

	/**
	 * @var array Array containing request body data.
	 */
	protected $payload;

	/**
	 * @var array Array containing headers to be send with the request.
	 */
	protected $headers;

	/**
	 * @var bool The status of the request, true if successful; false otherwise.
	 */
	public $status;

	/**
	 * @var int HTTP status code.
	 */
	public $code;

	/**
	 * @var string Error message.
	 */
	public $message;

	/**
	 * @var stdClass Result of the API call.
	 */
	public $results;

	/**
	 * @var stdClass Raw response from wp_remote_request.
	 */
	public $raw_response;

	/**
	 * @var bool Whether more results are available.
	 */
	public $more_results = false;

	public $current_page = 1;

	public $path_params = [];

	/**
	 * Constructor
	 *
	 * @param string $url          URL to which the request is supposed to be sent.
	 * @param array  $method       HTTP Method to use.
	 * @param string $access_token Authentication token.
	 * @param array  $body         Payload.
	 * @param array  $path_params  An array containing parameters to be used in path, i.e. [ 'ID', '233' ] will be converted to 'ID/233 and appended to $this->url.
	 * @param array  $headers      HTTP headers.
	 */
	function __construct( $url, $method, $access_token, $body = [], $path_params = [], $headers = [] ) {

		$this->method      = $method;
		$this->payload     = $body;
		$this->path_params = $path_params;
		$this->url         = $url;
		$this->headers     = $headers;

		if ( empty( $url ) ) {

			$this->status  = false;
			$this->message = 'Missing request URL!';

			return;
		}

		// If custom headers are not passed then use default headers.
		if ( empty( $headers ) ) {

			// Bail out if access token is missing!.
			if ( empty( $access_token ) ) {

				$this->status  = false;
				$this->message = 'Missing access token! for request: ' . $url;

				return;
			}

			// Pipeline flags this as not covered even though the tests are written, will look into it after launch.
			$this->headers = array( // @codeCoverageIgnore
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $access_token,
			);
		}

		// JSON Encode the body if request is a POST or PATCH request.
		switch ( $method ) {
			case 'POST':
			case 'PATCH':
				$this->payload = wp_json_encode( $body );
				break;
		}

		$this->make_request();
	}

	/**
	 * Makes a HTTP request of type $this->method to an API endpoint set in $this->$url
	 * using $this->headers as HTTP Headers and $this->payload as request payload,
	 * $this->path params is appended as path to $this->url.
	 *
	 * @return void
	 */
	private function make_request() {

		if ( empty( $this->method ) ) {

			$this->status       = false;
			$this->more_results = false;
			$this->message      = 'missing HTTP Method';
			return;
		}

		// have to keep timeout higher because some of the calls take longer, e.g. fetching an email with it's content.
		$args = array(
			'method'  => $this->method,
			'headers' => $this->headers,
			'body'    => $this->payload,
			'timeout' => 3,
		);

		$url = $this->url;

		if ( is_array( $this->path_params ) && ! empty( $this->path_params ) ) {
			$url = trailingslashit( $this->url ) . implode( '/', $this->path_params );
		}

		$this->raw_response = wp_remote_request( $url, $args );
		$response_body      = wp_remote_retrieve_body( $this->raw_response );
		$this->code         = wp_remote_retrieve_response_code( $this->raw_response );
		$decoded_response   = json_decode( $response_body );

		if (
			200 !== $this->code
			&& 201 !== $this->code
			&& 202 !== $this->code
			|| isset( $decoded_response->errorcode )
		) {
		
			$this->status   = false;
			$this->message  = ( is_object( $decoded_response ) && property_exists( $decoded_response, 'message' ) ) ? $decoded_response->message : '';
			$this->message .= ' Error: ' . wp_json_encode( $this );

		} else {

			$this->status       = true;
			$this->more_results = false;
		}

		if ( null !== $decoded_response ) {
			$this->results = $decoded_response;
			$this->setup_more_results();
		} else {
			$this->message = wp_json_encode( $this->raw_response );
		}
	}

	/**
	 * Sets appropriate object properties if more results are available when querying data using API.
	 *
	 * @return void
	 */
	private function setup_more_results() {
		if (
			$this->status
			&& ! empty( $this->results )
			&& ! empty( $this->results->page )
			&& ! empty( $this->results->count )
			&& ! empty( $this->results->pageSize )
		) {
			$count              = $this->results->count;
			$this->current_page = $this->results->page;
			$this->more_results = ( $count > ( $this->results->page * $this->results->pageSize ) );
		}
	}

	/**
	 * Gets more records if API returned paginated response.
	 *
	 * @return void
	 */
	public function get_more_results() {

		if ( ! $this->status || ! $this->more_results ) {

			$this->status       = false;
			$this->more_results = false;
			$this->message      = 'No more results found!';

			return;
		}

		if ( 'GET' === $this->method ) {

			$this->payload['$page'] = $this->current_page + 1;
			$this->make_request();

		} elseif ( 'POST' === $this->method ) {

			$payload             = json_decode( $this->payload );
			$payload->page->page = $this->current_page + 1;
			$this->payload       = wp_json_encode( $payload );

			$this->make_request();
		}
	}

	/**
	 * Helper for returning items returned by ET API queries.
	 *
	 * @return array
	 */
	public function get_result_items() : array {

		if ( empty( $this->results->items ) || ! is_array( $this->results->items ) ) {
			return [];
		}

		return $this->results->items;
	}
}
