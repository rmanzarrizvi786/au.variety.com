<?php

/**
 * Class Plugin
 *
 * Controller for accessing the GA API.
 *
 * @package pmc-google-analytics-bridge
 */

namespace PMC\Google_Analytics_Bridge;

use GAB\Query;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Google_Analytics_Bridge.
 */
class Plugin extends Query
{

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct()
	{

		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks()
	{
		add_filter('pmc_cheezcap_groups', [$this, 'pmc_cheezcap_groups']);

		add_filter(
			'gab_ga_authentication_mode',
			static function (): string {
				return 'service';
			}
		);
		add_filter(
			'gab_ga_service_account_details',
			[$this, 'get_service_account_details']
		);

		add_filter('pmc_ga_bridge_parse_trending_post_ids', [__CLASS__, 'parse_trending_post_ids'], 10, 2);
		add_filter('pmc_ga_bridge_parse_popular_post_ids', [__CLASS__, 'parse_popular_post_ids'], 10, 2);
	}

	/**
	 * @param $cheezcap_groups
	 *
	 * @return array
	 */
	public function pmc_cheezcap_groups($cheezcap_groups)
	{

		if (empty($cheezcap_groups) || !is_array($cheezcap_groups)) {
			$cheezcap_groups = [];
		}

		$cheezcap_groups[] = new \CheezCapGroup(
			__('PMC GA Bridge Settings', 'pmc-google-analytics-bridge'),
			'pmc_gab_settings',
			[
				new \CheezCapTextOption(
					__('GAB Project ID', 'pmc-google-analytics-bridge'),
					__('Google Analytics Bridge Project ID', 'pmc-google-analytics-bridge'),
					'pmc_gab_project_id',
					__('pmc-trending', 'pmc-google-analytics-bridge') //default
				),
				new \CheezCapTextOption(
					__('GAB Private Key ID', 'pmc-google-analytics-bridge'),
					__('Google Analytics Private Key ID', 'pmc-google-analytics-bridge'),
					'pmc_gab_private_key_id'
				),
				new \CheezCapTextOption(
					__('GAB Private Key', 'pmc-google-analytics-bridge'),
					__('Google Analytics Private Key', 'pmc-google-analytics-bridge'),
					'pmc_gab_private_key'
				),
				new \CheezCapTextOption(
					__('GAB Client Email', 'pmc-google-analytics-bridge'),
					__('Google Analytics Client Email', 'pmc-google-analytics-bridge'),
					'pmc_gab_client_email'
				),
				new \CheezCapTextOption(
					__('GAB Client ID', 'pmc-google-analytics-bridge'),
					__('Google Analytics Client ID', 'pmc-google-analytics-bridge'),
					'pmc_gab_client_id'
				),
				new \CheezCapTextOption(
					__('GAB Client x509 Cert Url', 'pmc-google-analytics-bridge'),
					__('Google Analytics Client x509 Cert Url', 'pmc-google-analytics-bridge'),
					'pmc_gab_client_x509_cert_url'
				),
			]
		);

		return $cheezcap_groups;
	}

	public function get_service_account_details(): array
	{
		return [
			'type'                        => 'service_account',
			'project_id'                  => \cheezcap_get_option('pmc_gab_project_id'),
			'private_key_id'              => \cheezcap_get_option('pmc_gab_private_key_id'),
			'private_key'                 => \cheezcap_get_option('pmc_gab_private_key'),
			'client_email'                => \cheezcap_get_option('pmc_gab_client_email'),
			'client_id'                   => \cheezcap_get_option('pmc_gab_client_id'),
			'auth_uri'                    => 'https://accounts.google.com/o/oauth2/auth',
			'token_uri'                   => 'https://oauth2.googleapis.com/token',
			'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
			'client_x509_cert_url'        => \cheezcap_get_option('pmc_gab_client_x509_cert_url'),
		];
	}

	/**
	 * Gets realtime trending post ids.
	 */
	public static function get_trending_post_ids(): array
	{
		$ids = (new Cache(
			'pmc_ga_bridge_parse_trending_post_ids',
			[
				'metrics'     => 'rt:pageviews',
				'dimensions'  => 'rt:pagePath',
				'max-results' => 75,
				'sort'        => '-rt:pageviews',
			],
			15
		)
		)->get();

		$ids = apply_filters('pmc_google_analytics_bridge_trending_ids', $ids);

		return is_array($ids) ? $ids : [];
	}

	/**
	 * Retrieve array of trending post IDs.
	 *
	 * @param array $ids           Initial post IDs, generally empty.
	 * @param array $callback_args GA PI arguments.
	 * @return array
	 */
	public static function parse_trending_post_ids(array $ids, array $callback_args): array
	{
		$response = self::get_google_analytics_realtime_data($callback_args);
		if (is_wp_error($response)) {
			return [];
		}
		$post_ids = [];
		if (!empty($response['rows'])) {
			$post_ids = self::_get_post_ids_from_ga_v3_response($response);
		}

		return $post_ids;
	}

	/**
	 * Gets popular posts filtered to some dimension.
	 *
	 * @param array $request_args Arguments to include in the API request.
	 * @return array
	 */
	public static function get_popular_posts(array $request_args): array
	{
		$default_args = array(
			'dimensions' => array(
				array(
					'name' => 'ga:pagePath',
				),
				array(
					'name' => 'ga:dimension3', // Post id.
				),
			),
			'metrics'    => array(
				array(
					'expression' => 'ga:pageviews',
				),
			),
			'orderBys'   => array(
				array(
					'fieldName' => 'ga:pageviews',
					'sortOrder' => 'DESCENDING',
				),
			),
			'pageSize'   => 75,
		);
		$request_args = array_merge($default_args, $request_args);
		// This variable is actually used but PHPCS is erroring.
		// phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UnusedVariable
		$full_request = array(
			'reportRequests' => array(
				$request_args,
			),
		);

		$ids = (new Cache(
			'pmc_ga_bridge_parse_popular_post_ids',
			$full_request,
			120
		)
		)->get();

		return is_array($ids) ? $ids : [];
	}

	/**
	 * Retrieve array of popular post IDs.
	 *
	 * Used as a cron callback.
	 *
	 * @param array $ids           Initial post IDs, generally empty.
	 * @param array $callback_args GA API arguments.
	 * @return array
	 */
	public static function parse_popular_post_ids(array $ids, array $callback_args): array
	{
		$response = self::get_google_analytics_v4_data($callback_args);
		if (is_wp_error($response)) {
			return array();
		}
		if (empty($response['reports'][0]['data']['rows'])) {
			return array();
		}
		// This variable is actually used but PHPCS is erroring.
		// phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UnusedVariable
		$post_ids = array();
		// This variable is actually used but PHPCS is erroring.
		// phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UnusedVariable
		foreach ($response['reports'][0]['data']['rows'] as $row) {
			list($path, $post_id) = $row['dimensions'];
			if (defined('WPCOM_IS_VIP_ENV') && WPCOM_IS_VIP_ENV) {
				// Cannot cover due to constant.
				$post_ids[] = $post_id; // @codeCoverageIgnore
			} else {
				// Need to look up the correct post_id locally
				// Ensure AMP urls are fetched based off their real permalink
				if ('/amp/' === substr($path, -5)) {
					$path = substr($path, 0, -4); // Leave the trailing slash
				}
				$post_id = url_to_postid(home_url($path));
				if ($post_id) {
					$post_ids[] = $post_id;
				}
			}
		}
		return $post_ids;
	}

	/**
	 * Gets post ids, prioritized by date, from a GA v3 API response.
	 *
	 * @param array $response Response from the GA v3 API.
	 * @return array
	 */
	protected static function _get_post_ids_from_ga_v3_response(array $response): array
	{
		$post_ids = [];
		if (!empty($response['rows'])) {
			foreach ($response['rows'] as $row) {
				$post_id = false;
				if (count($row) === 2) {
					[$uri] = $row;
				} elseif (count($row) === 3) {
					[$uri, $post_id] = $row;
				} else {
					continue;
				}
				// post_id is correct on WPCOM
				if ($post_id && defined('WPCOM_IS_VIP_ENV') && WPCOM_IS_VIP_ENV) {
					// Cannot cover due to constant.
					$post_ids[] = $post_id; // @codeCoverageIgnore
				} else {
					// Need to look up the correct post_id locally
					// Ensure AMP urls are fetched based off their real permalink
					if ('/amp/' === substr($uri, -5)) {
						$uri = substr($uri, 0, -4); // Leave the trailing slash
					}
					$post_id = url_to_postid(home_url($uri));
					if ($post_id) {
						$post_ids[] = $post_id;
					}
				}
			}
		}

		return $post_ids;
	}
}
