<?php
/**
 * Assorted utilities.
 *
 * @package pmc-pwa
 */
namespace PMC\PWA;

use WP_Service_Workers;

/**
 * Class Utils.
 */
class Utils {
	/**
	 * Is this request for a service-worker endpoint?
	 *
	 * @param int $scope Scope to check. See WP_Service_Workers.
	 * @return bool
	 */
	public static function is_service_worker_request( int $scope = WP_Service_Workers::SCOPE_ALL ): bool {
		switch ( $scope ) {
			case WP_Service_Workers::SCOPE_FRONT:
				return static::_is_service_worker_request_front();

			case WP_Service_Workers::SCOPE_ADMIN:
				return static::_is_service_worker_request_admin();

			case WP_Service_Workers::SCOPE_ALL:
			default:
				return static::_is_service_worker_request_front()
					|| static::_is_service_worker_request_admin();
		}
	}

	/**
	 * Helper to determine if this is a front-end service-worker request.
	 *
	 * @return bool
	 */
	protected static function _is_service_worker_request_front(): bool {
		if ( is_admin() ) {
			return false;
		}

		return WP_Service_Workers::SCOPE_FRONT === get_query_var(
			WP_Service_Workers::QUERY_VAR
		);
	}

	/**
	 * Helper to determine if this is an admin service-worker request.
	 *
	 * @return bool
	 */
	protected static function _is_service_worker_request_admin(): bool {
		if ( ! wp_doing_ajax() ) {
			return false;
		}

		// PWA plugin does not nonce these requests.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( empty( $_REQUEST['action'] ) ) {
			return false;
		}

		return 'wp_service_worker' === sanitize_text_field( $_REQUEST['action'] );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Restrict plugin to certain sites on VIP Go. VIP Classic's CDN is not
	 * supported, nor are sites we host on our own infrastructure.
	 *
	 * @codeCoverageIgnore Cannot cover due to use of constants.
	 *
	 * @return bool
	 */
	public static function plugin_is_supported(): bool {
		if (
			( defined( 'IS_UNIT_TEST' ) && true === IS_UNIT_TEST )
			|| class_exists( '\WP_UnitTestCase', false )
		) {
			return true;
		}

		// GD has its own iOS app that uses web views, we shouldn't interfere.
		if ( defined( 'PMC_SITE_NAME' ) && 'goldderby' === PMC_SITE_NAME ) {
			return false;
		}

		return defined( 'PMC_IS_VIP_GO_SITE' ) && PMC_IS_VIP_GO_SITE;
	}
}
