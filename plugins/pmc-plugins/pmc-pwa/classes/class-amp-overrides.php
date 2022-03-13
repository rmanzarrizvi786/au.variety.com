<?php
/**
 * Override functionality from the AMP plugin.
 *
 * @package pmc-pwa
 */

namespace PMC\PWA;

use AMP_Service_Worker;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class AMP_Overrides.
 */
class Amp_Overrides {
	use Singleton;

	const INSTALL_SERVICE_WORKER_SLUG = 'wp_serviceworker_install_amp';

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
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );

		// Needs to be 11 priority because we are removing actions from AMP.
		add_action( 'wp', [ $this, 'override_install_service_worker' ], 11 );
	}

	/**
	 * Remove actions from the AMP plugin and add our own.
	 */
	public function override_install_service_worker(): void {

		if ( ! \PMC::is_amp() ) {
			return;
		}

		remove_action( 'wp_footer', [ 'AMP_Service_Worker', 'install_service_worker' ] );
		remove_action( 'amp_post_template_footer', [ 'AMP_Service_Worker', 'install_service_worker' ] );

		add_action( 'wp_footer', [ $this, 'install_service_worker' ] );
		add_action( 'amp_post_template_footer', [ $this, 'install_service_worker' ] );
	}

	/**
	 * Create a rewrite to use in place of the service worker query
	 * string for the iframe contents.
	 */
	public static function add_rewrite_rules(): void {
		add_rewrite_rule( '^' . self::INSTALL_SERVICE_WORKER_SLUG . '/?$', 'index.php?amp_install_service_worker_iframe=1', 'top' );
	}


	/**
	 * Helper to return the URL for the iframe SRC.
	 */
	public static function get_install_service_worker_url(): string {
		return user_trailingslashit(
			home_url( self::INSTALL_SERVICE_WORKER_SLUG, 'https' )
		);
	}

	/**
	 * Override AMP's install service worker.
	 *
	 * @see wp_print_service_workers()
	 * @see AMP_Service_Worker::install_service_worker()
	 */
	public function install_service_worker(): void {

		// Checking existence of core functions and cannot cover.
		// @codeCoverageIgnoreStart
		if ( ! function_exists( 'wp_service_workers' ) || ! function_exists( 'wp_get_service_worker_url' ) ) {
			return;
		}
		// @codeCoverageEnd

		$src        = wp_get_service_worker_url( \WP_Service_Workers::SCOPE_FRONT );
		$iframe_src = $this->get_install_service_worker_url();
		?>
		<amp-install-serviceworker
			data-scope="/"
			src="<?php echo esc_url( $src ); ?>"
			data-iframe-src="<?php echo esc_url( $iframe_src ); ?>"
			layout="nodisplay"
		>
		</amp-install-serviceworker>
		<?php
	}

}
