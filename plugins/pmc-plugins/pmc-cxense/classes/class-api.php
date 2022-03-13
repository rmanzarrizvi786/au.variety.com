<?php
namespace PMC\Cxense;

use PMC\Global_Functions\Classes\PMC_Api;

class Api {

	const BASE_URI = 'https://api.cxense.com/';   // Cxenxe api url.

	/**
	 * Cxense username.
	 *
	 * @var string
	 */
	private $_user_name;

	/**
	 * Cxense user apikey.
	 *
	 * @var string
	 */
	private $_api_key;

	/**
	 * Api constructor.
	 *
	 * @param $user_name
	 * @param $api_key
	 */
	public function __construct( $user_name, $api_key ) {

		$this->_user_name = $user_name;
		$this->_api_key   = $api_key;
	}

	/**
	 * Return cxense Autentication header.
	 *
	 * @return array
	 */
	private function _generate_auth_header(): array {

		$date         = gmdate( 'Y-m-d\TH:i:s.000O' );
		$signature    = hash_hmac( 'sha256', $date, $this->_api_key );
		$header_value = "username=$this->_user_name date=$date hmac-sha256-hex=$signature";
		$header       = [ 'X-cXense-Authentication' => $header_value ];

		return $header;
	}

	/**
	 * Function to set end point and data for api request.
	 *
	 * @param $endpoint
	 * @param array $data
	 * @return mixed
	 */
	public function get_data( $endpoint, $data = [] ) {
		return $this->call_api( $endpoint, $data );
	}

	/**
	 * Function to make cxense api call.
	 *
	 * @param $endpoint
	 * @param $data
	 * @return mixed
	 */
	protected function call_api( $endpoint, $data ) {

		$api_url = apply_filters( 'pmc_cxense__api_url', self::BASE_URI . ltrim( $endpoint, '/' ) );

		$url_args = [
			'headers' => $this->_generate_auth_header(),
			'body'    => wp_json_encode( $data, true ),
			'timeout' => 3,
		];
		$response = PMC_Api::get_instance()->post( $api_url, $url_args );

		if ( ! $response ) {
			return $response;
		}

		return json_decode( $response->body, false );

	}

}

