<?php
/**
 * CDS API class
 * @since 2017-05-09 Amit Sannad
 */

namespace PMC\Cds;

use \PMC\Global_Functions\Traits\Singleton;

class Api {

	use Singleton;

	private $_url = 'https://service.mycdsglobal.com/ws/service/';

	protected function __construct() {
		add_filter( 'pmc_cheezcap_groups', array( $this, 'set_cheezcap_group' ) );
	}

	/**
	 * Add cheezcap options
	 *
	 * @param array $cheezcap_groups
	 *
	 * @return array
	 */
	public function set_cheezcap_group( $cheezcap_groups = [] ) {

		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = array();
		}

		// Needed for compatibility with BGR_CheezCap
		if ( class_exists( 'BGR_CheezCapGroup' ) ) {
			$cheezcap_group_class = 'BGR_CheezCapGroup';
		} else {
			$cheezcap_group_class = 'CheezCapGroup';
		}

		$cheezcap_options = [

			new \CheezCapTextOption( 'CDS App ID', 'CDS App ID', 'pmc_cds_app_id', '' ),

			new \CheezCapTextOption( 'CDS App Pasword', 'CDS App password', 'pmc_cds_app_pwd', '' ),

		];

		$cheezcap_groups[] = new $cheezcap_group_class( 'CDS API', 'pmc_cds_group', $cheezcap_options );

		return $cheezcap_groups;

	}

	/**
	 * Make url to call API
	 *
	 * @param string $url_fragment
	 *
	 * @return bool|string
	 */
	private function _get_url( $url_fragment = '' ) {

		// @codeCoverageIgnoreStart
		// @TODO: We should NOT do this on production code, since this plugin will go away, let's it be as is for now.
		if ( ! defined( 'IS_UNIT_TESTING' ) ) {
			if ( empty( $url_fragment ) ) {
				return false;
			}
		}
		// @codeCoverageIgnoreEnd

		$app_id  = get_option( 'cap_pmc_cds_app_id', '' );
		$app_pwd = get_option( 'cap_pmc_cds_app_pwd', '' );

		// @codeCoverageIgnoreStart
		if ( ! defined( 'IS_UNIT_TESTING' ) ) {
			if ( empty( $app_id ) || empty( $app_pwd ) ) {
				return false;
			}
		}
		// @codeCoverageIgnoreEnd

		$url = $this->_url . ltrim( $url_fragment, '/' );
		$url = add_query_arg(
			[
				'appId'  => $app_id,
				'pwd'    => $app_pwd,
				'format' => 'json',
			],
			$url
		);

		return apply_filters( 'pmc_cds__api_url', $url );
	}

	/**
	 * Make API Call to CDS
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return bool|stdClass
	 */
	private function _make_api_call( string $url = '', array $options = [] ) {

		$url = esc_url_raw( $url );

		//Changing timeout to 20 seconds since 10 seconds ET is slow to respond and alerts are failing.
		$options = wp_parse_args(
			$options,
			array(
				'method'      => 'GET',
				'timeout'     => 10,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers'     => array(),
				'body'        => array(),
				'cookies'     => array(),
				'sslverify'   => false,
			)
		);

		$error  = '';
		$method = strtolower( $options['method'] );

		if ( 'post' === $method ) {

			try {
				$response = wp_remote_post( $url, $options );
			}
			catch ( \Exception $e ) {
				$error .= 'Exception received while posting ' . $url;
				return (object) [
					'body'     => [ 'error' => $error ],
					'httpcode' => 500,
				];
			}
		} else {

			try {
				if ( ! empty( $options['body'] ) ) {
					$url = add_query_arg( $options['body'], $url );
				}
				$response = vip_safe_wp_remote_get( $url, '', 3, 10, 20, $options );
			}
			catch ( \Exception $e ) {
				$error .= 'Exception received while posting ' . $url;
				return (object) [
					'body'     => [ 'error' => $error ],
					'httpcode' => 500,
				];
			}

		}

		if ( is_wp_error( $response ) ) {

			$error .= 'Bad response received from ' . $url;

		} else {
			return (object) [
				'body'     => json_decode( $response['body'], true ),
				'httpcode' => $response['response']['code'],
			];
		}

		return (object) [
			'body'     => [ 'error' => $error ],
			'httpcode' => 500,
		];
	}

	/**
	 * Get product promotion data.
	 *
	 * @param $product string  The product ID to check against, e.g. 'ww3'.
	 * @param $promo_key string The promotion key to check for, e.g. '87b1dlnt3'.
	 * @param string $cache_group
	 *
	 * @return array An array of response data from the API on success else an empty array.
	 */
	public function get_promotion_data( string $product = '', string $promo_key = '', string $cache_group = 'pmc_promo_key' ): array {

		if ( empty( $product ) || empty( $promo_key ) ) {
			return [];
		}

		$cache_key    = $product . '_' . $promo_key;
		$url_fragment = sprintf( '/key/%s/%s', $product, $promo_key );
		$url          = $this->_get_url( $url_fragment );

		if ( $url ) {

			$pmc_cache   = new \PMC_Cache( $cache_key, $cache_group );
			$cached_data = $pmc_cache->expires_in( 604800 )// 7 days
									->updates_with(
										function () use ( $url ) {
											return $this->_make_api_call( $url );
										}
									)
									->get();

			if ( 200 === $cached_data->httpcode ) {
				return $cached_data->body;
			}

		}

		return [];
	}
}

// EOF
