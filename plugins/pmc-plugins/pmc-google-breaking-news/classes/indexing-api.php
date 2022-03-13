<?php
/**
 * Class contains Google Breaking News API related functions.
 *
 */
namespace PMC\Google_Breaking_News;

use PMC;
use \PMC\Global_Functions\Traits\Singleton;


class Indexing_API {

	use Singleton;

	const ACCESS_TOKEN_CACHE_KEY   = '_pmc_gnews_access_token';
	const RESPONSE_CACHE_KEY       = '_pmc_gnews_indexing_response';
	const REQUEST_CACHE_KEY        = '_pmc_gnews_indexing_request';
	const INDEXING_ENDPOINT        = 'https://indexing.googleapis.com/v1/index/public:update';
	const TOKEN_REQUEST_ENDPOINT   = 'https://www.googleapis.com/oauth2/v4/token';
	const REQUEST_SCOPE            = 'https://www.googleapis.com/auth/indexing';


	/**
	 * Initiate request to Google API
	 * @param $content
	 * @return bool
	 */
	public function init_request( $content ) {
		$payload = $this->get_auth_configs();
		if ( $payload === false ) {
			return false;
		}

		if ( array_key_exists( 'private_key', $payload ) ) {
			$key = $payload['private_key'];
			unset( $payload['private_key'] );
		}
		return $this->indexing_request( $content, $payload, $key );
	}


	/**
	 * Making indexing request to Google API
	 * @param $content
	 * @param $payload
	 * @param $key
	 * @return mixed bool / Wp Error
	 */
	public function indexing_request( $content, $payload, $key ) {
		$access_token = $this->_get_access_token();
		if ( empty( $access_token ) ) {
			$jwtoken = $this->get_jwt_token( $payload, $key, 'RS256' );
			if ( ! empty ( $jwtoken ) && ! is_wp_error( $jwtoken ) ) {
				$access_token = $this->request_access_token( Indexing_API::TOKEN_REQUEST_ENDPOINT, $jwtoken );
			}
		}

		if ( ! empty ( $access_token ) && ! is_wp_error( $access_token ) ) {
			$status = $this->content_index_request( Indexing_API::INDEXING_ENDPOINT, $access_token, $content );
			return $status;
		} else {
			return false;
		}
	}


	/**
	 * Get the API settings from options
	 * @return mixed array/false
	 */
	public function get_auth_configs() {
		$pmc_cheezcap  = \PMC_Cheezcap::get_instance();
		$client_email  = trim( $pmc_cheezcap->get_option( Plugin::CLIENT_EMAIL ) );
		$private_key   = trim( $pmc_cheezcap->get_option( Plugin::PRIVATE_KEY ) );
		$token_time    = time();
		if ( empty( $client_email ) || empty( $private_key ) ) {
			return false;
		}

		$payload = array(
			'iss'        => $client_email,
			'scope'      => Indexing_API::REQUEST_SCOPE,
			'aud'        => Indexing_API::TOKEN_REQUEST_ENDPOINT,
			'iat'        => $token_time,
			'exp'        => $token_time + HOUR_IN_SECONDS,
			'private_key'=> $private_key,

		);

		return $payload;
	}


	/**
	 * Getting JWT Token
	 * @param $payload
	 * @param $key
	 * @param string $alg
	 * @return string
	 */
	public function get_jwt_token( $payload, $key, $alg = 'RS256' ) {
		try {
			$token = JWT::encode( $payload, $key, $alg );
		} catch ( DomainException $e ) {

			wp_cache_set( self::RESPONSE_CACHE_KEY, $e->getMessage() );
			return new \WP_Error( 'gnews-access-token', esc_html__( $e->getMessage(), 'pmc-plugins' ) );
		} catch ( Exception $e ) {
			wp_cache_set( self::RESPONSE_CACHE_KEY, $e->getMessage() );
			return new \WP_Error( 'gnews-access-token', esc_html__( $e->getMessage(), 'pmc-plugins' ) );
		}
		return $token ;
	}

	/**
	 * Send request to Google to get Access Token
	 * @param $url
	 * @param $jwtoken
	 * @return mixed bool/ WP Error
	 */
	public function request_access_token( $url, $jwtoken ) {
		$params = array (
			'grant_type'   => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
			'assertion'    => trim( $jwtoken ),
			'access_type'  => 'offline'
		);

		//TODO:: Need to check why refresh_token is not returning  in response for this service

		$response = wp_remote_post( $url, array(
				'method'   => 'POST',
				'headers'  => array( 'Cache-Control' => 'no-store', 'Content-Type '=>' application/x-www-form-urlencoded' ),
				'body'     => $params
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_cache_set( self::RESPONSE_CACHE_KEY, $response->get_error_message() );
			return false;
		} else {
			$response_body = wp_remote_retrieve_body( $response );
			$response_message = wp_remote_retrieve_response_message( $response );
			$response_code = wp_remote_retrieve_response_code( $response );
			wp_cache_set( self::RESPONSE_CACHE_KEY, $response_body );
			$response_body = ! empty( $response_body ) ? json_decode( $response_body ) : '';

			if ( isset( $response_body->access_token )
				&& ! empty( $response_body->access_token )
				&& 200 === $response_code
			) {
				//Save access token to use for next request
				$this->_save_access_token( $response_body );
				return $response_body->access_token;
			} else {
				$error_msg = ! empty( $response_message ) ? $response_message : ' Unknown error occurred';
				wp_cache_set( self::RESPONSE_CACHE_KEY, $response_code . ' : ' . $error_msg );
				return false;
			}
		}

	}

	/**
	 * Send Request to Google API to index the content
	 * @param $url
	 * @param $access_token
	 * @param $content
	 * @return mixed bool / Wp Error
	 */
	public function content_index_request( $url, $access_token, $content = '' ) {
		wp_cache_set( self::REQUEST_CACHE_KEY, $content );
		$response = wp_remote_post( $url, array(
			'body'    => $content,
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json; charset=UTF-8',
			),
		) );

		if ( is_wp_error( $response ) ) {
			wp_cache_set( self::RESPONSE_CACHE_KEY, $response->get_error_message() );
			return false;
		}

		wp_cache_set( self::RESPONSE_CACHE_KEY, wp_remote_retrieve_body( $response ) );
		$response_message = wp_remote_retrieve_response_message( $response );
		if ( strtolower( $response_message ) == 'ok' && 200 === wp_remote_retrieve_response_code( $response ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if the access token is valid
	 * @param $access_token_details
	 * @return bool
	 */
	private function _is_access_token_expired( $access_token_details ) {
		$access_token_details['expire_time'];
		if ( empty( $access_token_details['expire_time'] ) || $access_token_details['expire_time'] < ( time() + MINUTE_IN_SECONDS ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Save access token in db
	 * @param $access_token
	 */
	private function _save_access_token( $access_token ) {
		pmc_update_option( Indexing_API::ACCESS_TOKEN_CACHE_KEY, array(
			'access_token'  => trim( $access_token->access_token ),
			'expire_time'   => time() + ( int ) $access_token->expires_in,
		) );

	}

	/**
	 * Get the access token from db and also checks if it is expired
	 * @return mixed $access_token | null
	 */
	private function _get_access_token() {
		$access_token_details = pmc_get_option( Indexing_API::ACCESS_TOKEN_CACHE_KEY );
		if ( empty( $access_token_details ) ) {
			return null;
		}
		$is_token_expired = $this->_is_access_token_expired( $access_token_details );

		if ( $is_token_expired === false ) {
			return $access_token_details['access_token'];
		} else {
			return null;
		}
	}

}

// EOF
