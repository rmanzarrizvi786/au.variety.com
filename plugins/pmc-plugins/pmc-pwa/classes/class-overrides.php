<?php
/**
 * Override other PMC plugins et al.
 *
 * @package pmc-pwa
 */

namespace PMC\PWA;

use OneSignal_Public;
use PMC\Global_Functions\Smart_App_Banners;
use PMC\Global_Functions\Traits\Singleton;
use WP_Service_Workers;

/**
 * Class Overrides.
 */
class Overrides {
	use Singleton;

	/**
	 * Overrides constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'wp_head', [ $this, 'suppress_add_to_home_prompt' ] );

		// Run after themes make their customizations.
		add_filter( 'web_app_manifest', [ $this, 'merge_smart_app_banners_manifest' ], 99 );

		// Defer to the PWA's manifest, which includes the Smart App Banner data.
		add_filter( 'pmc_global_smart_banner_link_to_web_app_manifest', '__return_false' );

		add_action( 'wp_front_service_worker', [ $this, 'disable_for_firefox' ], PHP_INT_MIN );
		add_action( 'wp_front_service_worker', [ $this, 'disable_for_firefox_closer' ], PHP_INT_MAX );
	}

	/**
	 * Prevent mobile browsers from displaying the native "Add to Home" prompt,
	 * as it interferes with the OneSignal prompt.
	 *
	 * @link https://web.dev/customize-install/#beforeinstallprompt
	 */
	public function suppress_add_to_home_prompt(): void {
		?>
		<script type="text/javascript">
			window.addEventListener('beforeinstallprompt', (e) => {
				e.preventDefault();
			});
		</script>
		<?php
	}

	/**
	 * Add Smart App Banners' manifest data, preferring PWA and theme
	 * customizations.
	 *
	 * @param array $manifest Web app manifest.
	 * @return array
	 */
	public function merge_smart_app_banners_manifest( array $manifest ): array {
		$additions = Smart_App_Banners::get_instance()->manifest_json( true );

		return array_merge( $additions, $manifest );
	}

	/**
	 * Disable all PWA functions for Firefox.
	 *
	 * OneSignal remains, but Firefox has abandoned PWA support on desktop and
	 * it comprises less than 2% of traffic on major brands, so we'll disable
	 * it.
	 *
	 * @link https://bugzilla.mozilla.org/show_bug.cgi?id=1682593
	 */
	public function disable_for_firefox(): void {
		wp_register_service_worker_script(
			'sad-firefox',
			[
				'src'  => static function (): void {
					// Closed in a separate "script."
					?>
					if ( -1 === navigator.userAgent.toLowerCase().indexOf('firefox') ) {
					<?php
				},
			]
		);
	}

	/**
	 * Disable all PWA functions for Firefox.
	 *
	 * See docblock on `::disable_for_firefox()` for details.
	 */
	public function disable_for_firefox_closer(): void {
		wp_register_service_worker_script(
			'sad-firefox-close',
			[
				'src' => static function (): void {
					// Opened in a separate "script."
					?>
					}
					<?php
				},
			]
		);
	}
}
