<?php

namespace PMC\Global_Functions\Classes;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class with common methods used in API
 *
 * Class PMC_Api
 * @package PMC\Global_Functions\Classes
 */
class PMC_Api {
	use Singleton;

	/**
	 * Generate authorization headers - hash_hmac signature
	 *
	 * @param $key string
	 * @param $secret string
	 *
	 * @return  array
	 */
	public function get_hmac( string $key = '', string $secret = '' ): array {

		if ( empty( $key ) || empty( $secret ) ) {
			return [];
		}

		$date = gmdate( 'D, d M Y H:i:s' ) . ' GMT'; // format - Tue, 26 Mar 2019 23:54:27 GMT

		// Generate auth header
		$date_text = "date: $date";
		$hash      = hash_hmac( 'sha1', $date_text, $secret, true );
		$signature = rawurlencode( base64_encode( $hash ) );
		$auth      = "Signature keyId=\"$key\",algorithm=\"hmac-sha1\",signature=\"$signature\"";

		// return HTTP header
		return [
			'Content-Type'  => 'application/json',
			'Authorization' => $auth,
			'Date'          => $date,
		];
	}

	/**
	 * Make an API Call using POST method
	 *
	 * @param string $url
	 * @param array  $args
	 *
	 * @return false|object
	 */
	public function post( string $url = '', $args = [] ) {

		if ( empty( $url ) ) {
			return false;
		}

		$url = esc_url_raw( $url );

		$default_args = [
			'timeout'     => 3,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => [],
			'body'        => [],
			'cookies'     => [],
			'sslverify'   => true,
		];

		$args = array_merge( $default_args, $args );

		try {
			$response = wp_remote_post( $url, $args );
		} catch ( \Exception $e ) {
			$error = 'Exception received is ' . $e->getMessage();
		}

		if ( ! empty( $error ) || empty( $response ) || is_wp_error( $response ) || 200 !== $response['response']['code'] ) {
			return false;
		}

		return (object) [
			'body'     => $response['body'],
			'httpcode' => $response['response']['code'],
		];
	}

}
