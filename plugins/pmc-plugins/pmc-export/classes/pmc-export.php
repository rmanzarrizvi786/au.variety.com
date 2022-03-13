<?php
namespace PMC\Export;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Main plugin class.
 * Responsible for common functionality and initializing report types.
 */
class PMC_Export {

	use Singleton;

	/**
	 * Main admin menu slug.
	 */
	const MENU_SLUG = 'reporting';

	protected function __construct() {

		add_action( 'init', [ $this, 'action_init' ] );

		/**
		 * Initialize CLI.
		 */
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			// Cant mock this scenario, no useful way to test the code
			require_once( __DIR__ . '/wp-cli/pmc-export-wp-cli.php' ); // @codeCoverageIgnore
		}

		/**
		 * Initialize all report classes here.
		 */
		Posts::get_instance();
	}

	/**
	 * Responsible for registering admin menu.
	 */
	public function action_init() {

		// Only if user have properly permission may continue
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_action( 'admin_init', [ $this, 'action_admin_init' ] );
		add_action( 'admin_menu', [ $this, 'action_admin_menu' ] );

	}

	public function action_admin_init() {
		add_action( 'admin_enqueue_scripts', [ $this, 'action_admin_enqueue_scripts' ] );
	}

	/**
	 * Register top level menu.
	 */
	public function action_admin_menu() {

		// We want to add the Reporting main menu to the nav if no exists
		\PMC::maybe_add_menu_page( 'Reporting', 'Reporting', 'manage_options', static::MENU_SLUG );

	}

	/**
	 * Enqueue common CSS / JS files for reporting.
	 * All the common assets that can be used across reports can be enqueued here.
	 */
	public function action_admin_enqueue_scripts( $hook ) {

		// Don't load the common scripts if the page doesn't belong to any of the Reporting menus.
		if ( false === strpos( $hook, 'reporting_page_' ) ) {
			return;
		}

		wp_register_script( 'pmc-export-ajax-api', plugins_url( 'pmc-export/assets/js/ajax-api.js', PMC_EXPORT_PLUGIN_DIR ), [ 'jquery', 'jquery-ui-progressbar' ], PMC_EXPORT_VERSION );
		wp_register_script( 'pmc-export-admin-ui', plugins_url( 'pmc-export/assets/js/admin-ui.js', PMC_EXPORT_PLUGIN_DIR ), [ 'pmc-export-ajax-api' ], PMC_EXPORT_VERSION );

		wp_enqueue_script( [ 'pmc-export-ajax-api', 'pmc-export-admin-ui' ] );

		wp_enqueue_style( 'jquery-ui-progressbar-css', 'https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'pmc-export-admin-css', plugins_url( 'pmc-export/assets/css/admin.css', PMC_EXPORT_PLUGIN_DIR ), [ 'jquery-ui-progressbar-css' ], PMC_EXPORT_VERSION );

	}

}
