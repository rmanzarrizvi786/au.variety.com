<?php

namespace Oriel;

/**
 * Class API - API helper for Oriel Service
 */
class API {


	public static $_domain;

	/**
	 * Gets a resource from Oriel servers (ex: /domain)
	 *
	 * @param  string $resource
	 * @return array|null
	 */
	public static function get( $resource ) {
		$settings = Oriel::get_settings();

		// No API Key set, do nothing
		if ( ! $settings->api_key ) {
			return null;
		}

		if ( substr( $resource, -1 ) !== '/' ) {
			$resource .= '/';
		}

		$url     = $settings->api_url . $resource;
		$headers = array(
			'Content-Type' => 'application/json',
		);

		$response = self::_call( $url, $headers );
		if ( $response && 200 === $response['status'] ) {
			// Return decoded JSON
			return json_decode( $response['body'], JSON_NUMERIC_CHECK );
		}

		return null;
	}

	/**
	 * Creates an http request with wp_remote_get or cURL to Oriel servers (ex: /domain)
	 *
	 * @param  string $resource
	 * @return array|null
	 */
	public static function _call( $url, $headers = array(), $timeout = 10 ) {
		$settings = Oriel::get_settings();

		// No API Key set, do nothing
		if ( ! $settings->api_key ) {
			return null;
		}

		$headers['X-SDK']         = $settings->sdk_header;
		$headers['X-SDK-V']       = $settings->sdk_version;
		$headers['AUTHORIZATION'] = 'Bearer ' . $settings->api_key;

		if ( function_exists( 'wp_remote_get' ) ) {
			// wp_remote_get: Make request and fetch data

			$request = wp_remote_get(
				$url, array(
					'timeout' => $timeout,
					'headers' => $headers,
				)
			);

			if ( ! is_wp_error( $request ) ) {
				   $status_code = wp_remote_retrieve_response_code( $request );
				   $body        = wp_remote_retrieve_body( $request );
				   $headers     = wp_remote_retrieve_headers( $request );

				   return array(
					   'status'  => $status_code,
					   'headers' => $headers,
					   'body'    => $body,
				   );
			} else {
				return null;
			}
		} elseif ( function_exists( 'curl_init' ) ) {
			// cURL: Make request and fetch data
			$curl = curl_init( $url );
			curl_setopt( $curl, CURLOPT_URL, $url );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true );
			curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 5 );
			curl_setopt( $curl, CURLOPT_TIMEOUT, $timeout );
			curl_setopt( $curl, CURLOPT_HEADER, true );
			curl_setopt( $curl, CURLOPT_ENCODING, 'gzip' );

			if ( isset( $headers['USER-AGENT'] ) ) {
				curl_setopt( $curl, CURLOPT_USERAGENT, $headers['USER-AGENT'] );
			}

			$header_lines = array();
			foreach ( $headers as $key => $value ) {
				$header_lines[] = "$key: $value";
			}
			curl_setopt( $curl, CURLOPT_HTTPHEADER, $header_lines );

			$response = curl_exec( $curl );

			if ( ! $response ) {
				return null;
			}

			$header_size  = curl_getinfo( $curl, CURLINFO_HEADER_SIZE );
			$header       = substr( $response, 0, $header_size );
			$header_lines = explode( "\r\n", $header );

			$headers = array();
			foreach ( $header_lines as $header ) {
				if ( strpos( $header, ':' ) ) {
					$header                                      = explode( ':', $header, 2 );
					$headers[ strtolower( trim( $header[0] ) ) ] = trim( $header[1] );
				}
			}

			$body        = substr( $response, $header_size );
			$status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
			curl_close( $curl );

			return array(
				'status'  => $status_code,
				'headers' => $headers,
				'body'    => $body,
			);
		}

		return null;
	}

	public static function get_loader() {
		$domain = self::get_domain_data();
		if ( $domain['is_stale'] ) {
			return '';
		}
		return $domain['loader_script'];
	}

	public static function get_head_key() {
		$domain = self::get_domain_data();
		if ( $domain['is_stale'] ) {
			return '';
		}
		return $domain['short_gw_key'];
	}

	public static function get_head_script() {
		$script = self::get_loader();
		if ( $script ) {
			return $script;
		}
		return null;
	}
	/**
	 * Gets data about the current domain and manages data caching for it. It contains a fault tolerant mechanism
	 * in case Oriel service is down
	 *
	 * @return array|mixed|null
	 */
	public static function get_domain_data() {
		if ( self::$_domain ) {
			return self::$_domain;
		}

		$cache    = Oriel::get_cache();
		$settings = Oriel::get_settings();

		// Get domain, check if it's stale or not (stale loader script doesn't get served to users)
		$domain = $cache->get( 'domain' );
		if ( $domain ) {
			// Update Cache TTL with fetched one
			if ( $settings->enable_remote_settings && isset( $domain['integration_settings'] )
				&& isset( $domain['integration_settings']['head_script_cache_ttl'] )
			) {
				$settings->head_script_cache_ttl = $domain['integration_settings']['head_script_cache_ttl'];
			}

			// Return data if we shouldn't be making a call
			if ( isset( $domain['last_call_made'] ) && time() - $domain['last_call_made'] < $settings->head_script_cache_ttl - 10 ) {
				self::$_domain = $domain;
				return $domain;
			}
		} else {
			// Domain data not stored yet, fresh installation
			$domain = array();
		}

		// Set as stale, loader script won't be served to users
		$domain['is_stale'] = true;

		// A request is already being made, return data
		$lock = $cache->get( 'domain_lock' );
		if ( $lock ) {
			self::$_domain = $domain;
			return $domain;
		}

		// Set lock (expires in 5 seconds) and make request
		$cache->set( 'domain_lock', 1, 5 );
		$new_domain = API::get( '/domain' );
		if ( $new_domain ) {
			// Set new data as fresh, update cache
			$domain             = $new_domain;
			$domain['is_stale'] = false;
		}

		// Remember when last call was made, update domain data
		$domain['last_call_made'] = time();
		$cache->set( 'domain', $domain );

		// Release lock
		$cache->delete( 'domain_lock' );

		self::$_domain = $domain;
		return $domain;
	}
}

