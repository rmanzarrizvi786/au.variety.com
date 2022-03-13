<?php
/**
 * Override various aspects of Core PWA feature.
 *
 * @package pmc-pwa
 */

namespace PMC\PWA;

use PMC\Global_Functions\Traits\Singleton;
use WP_Service_Worker_Scripts;

/**
 * Class Core_Overrides.
 */
class Core_Overrides {
	use Singleton;

	/**
	 * Query var used to render error templates.
	 */
	protected const ERROR_TEMPLATE_QUERY_VAR = 'wp_error_template';

	/**
	 * Plugin constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_filter( 'wp_https_detection_ui_disabled', '__return_true' );
		add_filter( 'pwa_flush_rules_on_admin_init', '__return_false' );
		add_action( 'wp_admin_service_worker', [ $this, 'remove_offline_commenting_support' ] );
		add_action( 'wp_front_service_worker', [ $this, 'remove_offline_commenting_support' ] );

		add_action( 'init', [ $this, 'add_rewrite_rules' ] );
		add_filter( 'wp_offline_error_precache_entry', [ $this, 'filter_error_template_urls' ] );
		add_filter( 'wp_server_error_precache_entry', [ $this, 'filter_error_template_urls' ] );

		// Hooked late to prevent other code from restoring the admin bar.
		// See function docblock for why the admin bar is suppressed on error pages.
		// phpcs:ignore WordPressVIPMinimum.UserExperience.AdminBarRemoval.RemovalDetected
		add_filter( 'show_admin_bar', [ $this, 'hide_admin_bar_on_error_pages' ], 999 );

		// Temporarily disable admin service worker while Safari and FF issues are debugged.
		add_action( 'wp_admin_service_worker', [ $this, 'disable_admin_sw' ], 0 );
		add_action( 'init', [ $this, 'remove_admin_sw_registration' ] );
	}

	/**
	 * Disable support for offline commenting as WP comments are rarely used.
	 *
	 * As `wp-offline-scripts` is a dependency of `wp-navigation-routing`, it
	 * must be re-registed with an empty output.
	 *
	 * @param WP_Service_Worker_Scripts $scripts Registered service-worker scripts.
	 */
	public function remove_offline_commenting_support( WP_Service_Worker_Scripts $scripts ): void {
		$handle = 'wp-offline-commenting';

		$scripts->remove( $handle );
		$scripts->register(
			$handle,
			[
				'src'  => '__return_empty_string',
				'deps' => [
					'wp-base-config',
				],
			]
		);
	}

	/**
	 * Add rewrites for nice permalink.
	 */
	public function add_rewrite_rules(): void {
		global $wp_rewrite;

		// Serve error pages from pretty URLs for Batcache compatibility.
		add_rewrite_rule(
			'^' . static::ERROR_TEMPLATE_QUERY_VAR . '/([^/]+)/?$',
			add_query_arg(
				static::ERROR_TEMPLATE_QUERY_VAR,
				'$matches[1]',
				$wp_rewrite->index
			),
			'top'
		);
	}

	/**
	 * Rewrite error template precache URLs to use pretty permalinks.
	 *
	 * @param array $entry Precache entry.
	 * @return array
	 */
	public function filter_error_template_urls( array $entry ): array {
		$args = [];

		wp_parse_str(
			wp_parse_url(
				$entry['url'],
				PHP_URL_QUERY
			),
			$args
		);

		$entry['url'] = home_url(
			static::ERROR_TEMPLATE_QUERY_VAR
			. '/'
			. $args[ static::ERROR_TEMPLATE_QUERY_VAR ]
		);
		$entry['url'] = user_trailingslashit( $entry['url'] );

		$entry['revision'] .= ';pmc_pretty_permalinks=1';

		return $entry;
	}

	/**
	 * Hide the admin bar on error pages.
	 *
	 * The PWA plugin unsets the user when rendering error pages to remove any
	 * elements that require authentication, but does not hide the admin bar.
	 * We remove the bar because none of its links work if WP is unavailable.
	 *
	 * @param bool $show Whether or not to show admin bar.
	 * @return bool
	 */
	public function hide_admin_bar_on_error_pages( bool $show ): bool {
		if ( is_offline() || is_500() ) {
			return false;
		}

		return $show;
	}

	/**
	 * Temporarily disable admin service worker while diagnosing conflicts with
	 * Firefox and Safari.
	 *
	 * @codeCoverageIgnore Cannot cover as it ends script execution.
	 */
	public function disable_admin_sw(): void {
		// Rule is confused by the JS comment syntax.
		// phpcs:ignore PmcWpVip.Usage.EnforceHttps
		wp_die( '// Disabled.' );
	}

	/**
	 * Prevent service-worker registration from appearing in the admin.
	 */
	public function remove_admin_sw_registration(): void {
		remove_action( 'admin_print_scripts', 'wp_print_service_workers', 9 );
		remove_action( 'login_footer', 'wp_print_service_workers', 9 );
	}
}
