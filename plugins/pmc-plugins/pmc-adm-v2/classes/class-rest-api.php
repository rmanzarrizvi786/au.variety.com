<?php
/**
 * REST API endpoints for Gutenberg integration.
 *
 * @package pmc-adm-v2
 */

namespace PMC\Adm;

use PMC_Ads;
use PMC\Global_Functions\Traits\Singleton;
use WP_REST_Response;

/**
 * Class REST_API.
 */
class REST_API {
	use Singleton;

	public const NAMESPACE = 'pmc/adm/v2';

	/**
	 * REST_API constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Register REST endpoints.
	 */
	public function register_endpoints(): void {
		// TODO: refactor to use global-functions helpers.
		register_rest_route(
			static::NAMESPACE,
			'locations-by-provider',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_locations_by_provider' ],
				'permission_callback' => [ $this, 'check_permissions' ],
			]
		);
	}

	/**
	 * Restrict access to users who can edit as endpoint is only used by
	 * Gutenberg blocks.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Retrieve array of ad locations grouped by provider.
	 *
	 * @return WP_REST_Response
	 */
	public function get_locations_by_provider(): WP_REST_Response {
		if ( ! class_exists( PMC_Ads::class, false ) ) {
			// Cannot unload class.
			return rest_ensure_response( [] ); // @codeCoverageIgnore
		}

		$potential_locations = $this->_get_locations_from_ads();

		// `$potential_locations` cannot be anything other than an array.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		$potential_locations = array_map( 'array_flip', $potential_locations );

		$response = [];

		foreach ( $potential_locations as $provider_id => &$provider_locations ) {
			$all_locations = PMC_Ads::get_instance()->get_locations( $provider_id );

			$provider_locations = array_intersect_key(
				$all_locations,
				$provider_locations
			);

			asort( $provider_locations, SORT_NATURAL );
		}

		foreach ( PMC_Ads::get_instance()->get_providers() as $provider ) {
			$id = $provider->get_ID();

			$filtered_locations = $potential_locations[ $id ] ?? [];

			$response[ $id ] = [
				'id'        => $id,
				'title'     => $provider->get_title(),
				'locations' => apply_filters(
					'pmc_adm_embeddable_ad_locations',
					$filtered_locations,
					$provider,
					$filtered_locations // Unfiltered list for reference in callbacks.
				),
			];
		}

		ksort( $response, SORT_NATURAL );

		return rest_ensure_response( array_values( $response ) );
	}

	/**
	 * Retrieve ad locations that include embeddable ads.
	 *
	 * @return array
	 */
	protected function _get_locations_from_ads(): array {
		$grouped = [];

		$ads = PMC_Ads::get_instance()->get_ads(
			true,
			'', // If not empty, meta query is ignored.
			'', // If not empty, meta query is ignored.
			[
				// Query is cached.
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query' => [
					[
						'key'     => '_ad_embeddable',
						'compare' => 'exists',
					],
				],
			]
		);

		foreach ( $ads as $ad ) {
			$provider = get_post_meta( $ad->ID, '_ad_provider', true );
			$location = get_post_meta( $ad->ID, '_ad_location', true );

			if ( ! isset( $grouped[ $provider ] ) ) {
				$grouped[ $provider ] = [];
			}

			$grouped[ $provider ][] = $location;
		}

		// `$grouped` is initialized as an array and never reassigned.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		return array_map( 'array_unique', $grouped );
	}
}
