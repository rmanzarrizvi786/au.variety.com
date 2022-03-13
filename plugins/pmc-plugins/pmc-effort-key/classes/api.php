<?php
/**
 *
 * Effort Key API class
 *
 * @since 2019-04-14 Archana Mandhare
 */

namespace PMC\Effort_Key;

use PMC\Global_Functions\Classes\PMC_Api;
use PMC\Global_Functions\Traits\Singleton;

class Api {

	use Singleton;

	protected function __construct() {
		add_filter( 'pmc_cheezcap_groups', array( $this, 'set_cheezcap_group' ) );
	}

	/**
	 * Add cheezcap options for the Effort Key API
	 *
	 * @param array $cheezcap_groups
	 *
	 * @return array
	 */
	public function set_cheezcap_group( $cheezcap_groups = [] ): array {

		// @codeCoverageIgnoreStart
		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = [];
		}

		// Needed for compatibility with BGR_CheezCap
		if ( class_exists( 'BGR_CheezCapGroup' ) ) {
			$cheezcap_group_class = 'BGR_CheezCapGroup';
		} else {
			$cheezcap_group_class = 'CheezCapGroup';
		}
		// @codeCoverageIgnoreEnd

		// @todo: we probably want to extend the CheezCapTextOption into a secured option. Possible using the pmc-options plugin which support encryption. Will need code from this PR: https://bitbucket.org/penskemediacorp/pmc-plugins/pull-requests/1924/pmc-oauth/diff

		$cheezcap_options = [

			new \CheezCapTextOption( 'Effort Key - URL', 'Effort Key - URL', 'pmc_effort_key_url', '' ),

			new \CheezCapTextOption( 'Effort Key - Auth Key', 'Effort Key - Auth Key', 'pmc_effort_key_auth_key', '' ),

			new \CheezCapTextOption( 'Effort Key - Auth Secret', 'Effort Key - Auth Secret', 'pmc_effort_key_auth_secret', '' ),

		];

		$cheezcap_groups[] = new $cheezcap_group_class( 'Effort Key API', 'pmc_effort_key_group', $cheezcap_options );

		return $cheezcap_groups;

	}

	/**
	 * Make an API Call using GET method
	 *
	 * @param string $url
	 * @param array  $headers
	 *
	 * @todo Move this function to pmc-global-functions so that this method can be used by other plugins too
	 * @return false|object
	 */
	private function _make_get_api_call( string $url = '', array $headers = [] ) {

		$error = '';

		if ( empty( $url ) ) {
			$error .= 'No URL for making the AIP call';
		} else {

			$url = esc_url_raw( $url );

			$options = [
				'method'      => 'GET',
				'timeout'     => 3,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers'     => $headers,
				'body'        => [],
				'cookies'     => [],
				'sslverify'   => true,
			];

			try {
				$response = vip_safe_wp_remote_get( $url, '', 3, 3, 20, $options );
			} catch ( \Exception $e ) {
				$error = 'Exception received is ' . $e->getMessage();
			}

			if ( ! empty( $error ) || empty( $response ) || is_wp_error( $response ) ) {
				return false;
			} elseif ( 200 === $response['response']['code'] ) {
				return (object) [
					'body'     => json_decode( $response['body'], true ),
					'httpcode' => $response['response']['code'],
				];
			}
		}

		return false;
	}

	/**
	 * Get product promotion data.
	 *
	 * @param string $effort_keys The promotion key to check for, e.g. 'WW4111193B7Q'. or 'WW4111193B7Q,WWR1234CSR12'
	 * @param string $cache_group
	 *
	 * @return array An array of response data from the API on success else an empty array.
	 *
	 */
	public function get_data( string $effort_keys = '', string $cache_group = 'pmc_effort_keys' ): array {

		if ( empty( $effort_keys ) ) {
			return [];
		}

		$app_key    = \PMC_Cheezcap::get_instance()->get_option( 'pmc_effort_key_auth_key', '' );
		$app_secret = \PMC_Cheezcap::get_instance()->get_option( 'pmc_effort_key_auth_secret', '' );
		$host_url   = \PMC_Cheezcap::get_instance()->get_option( 'pmc_effort_key_url', '' );

		if ( empty( $app_key ) || empty( $app_secret ) || empty( $host_url ) ) {
			return [];
		}

		$effort_keys = sanitize_text_field( $effort_keys );

		$url_fragment = "/effort-keys/$effort_keys";

		$url = rtrim( $host_url, '/' ) . '/' . ltrim( $url_fragment, '/' );

		$url = apply_filters( 'pmc_effort_key__api_url', $url );

		if ( empty( $url ) ) {
			return [];
		}

		$headers = PMC_Api::get_instance()->get_hmac( $app_key, $app_secret );

		$cache_key = 'pmc-effort_key_api-' . $effort_keys;

		if ( class_exists( 'PMC_Cache' ) ) {

			$pmc_cache = new \PMC_Cache( $cache_key, $cache_group );

			$cached_data = $pmc_cache->expires_in( 900 ) // 15 minutes
								->updates_with(
									function() use ( $url, $headers ) {
										return $this->_make_get_api_call( $url, $headers );
									}
								)
								->get();

			if ( false !== $cached_data && 200 === $cached_data->httpcode ) {
				return (array) $cached_data->body;
			} else {
				// If we come here invalidate the cache
				$pmc_cache->invalidate();
			}

		}

		return [];

	}
}

// EOF
