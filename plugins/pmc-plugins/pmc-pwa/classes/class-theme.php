<?php
/**
 * Theme support.
 *
 * @package pmc-pwa
 */

namespace PMC\PWA;

use PMC\Global_Functions\Traits\Singleton;
use WP;
use WP_Web_App_Manifest;

/**
 * Class Theme.
 */
class Theme {
	use Singleton;

	/**
	 * Theme constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		// Late priority so that we generally have the final word.
		add_action( 'after_setup_theme', [ $this, 'allow_supported_integrations' ], 99999 );

		add_action( 'parse_request', [ $this, 'redirect_legacy_manifest' ] );
	}

	/**
	 * Permit specific PWA integrations.
	 */
	public function allow_supported_integrations(): void {
		$feature = 'service_worker';

		add_theme_support(
			$feature,
			[
				'wp-scripts' => true,
				'wp-styles'  => true,
			]
		);
	}

	/**
	 * Redirect requests for previous Web App Manifest.
	 *
	 * @param WP $wp WP object.
	 */
	public function redirect_legacy_manifest( WP $wp ): void {
		if ( ! isset( $wp->query_vars['pagename'] ) ) {
			return;
		}

		$old_path       = get_stylesheet() . '/assets/app/manifest.json';
		$request_substr = substr(
			$wp->query_vars['pagename'],
			- strlen( $old_path )
		);

		if ( $request_substr !== $old_path ) {
			return;
		}

		wp_safe_redirect( WP_Web_App_Manifest::get_url(), 301 );
		// Cannot cover termination of execution.
		exit; // @codeCoverageIgnore
	}
}
