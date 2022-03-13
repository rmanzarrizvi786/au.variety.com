<?php
/**
 * Plugin Name: Oriel
 * Plugin URI: https://oriel.io/
 * Description: Oriel enables your website to collect ad block analytics and to communicate with your ad blocking audience.
 * Version: 8.8.8
 * Author: Oriel Ventures Limited
 * Author URI: https://oriel.io/
 * License: THE SOFTWARE CONTAINED IN THIS PACKAGE IS PROPERTY
			OF ORIEL VENTURES LIMITED.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ORIEL__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require_once ORIEL__PLUGIN_DIR . 'oriel/settings.php';
require_once ORIEL__PLUGIN_DIR . 'oriel/class-orielutil.php';
require_once ORIEL__PLUGIN_DIR . 'options.php';
require_once ORIEL__PLUGIN_DIR . 'sdk/oriel.php';

/**
 * Class OrielWP - WP Plugin entry point
 */
class OrielWP {


	public $plugin_name;
	public $plugin_slug;
	public $version;
	public $oriel;

	/**
	 * Oriel Constructor
	 */
	public function __construct() {
		$this->plugin_name = 'Oriel';
		$this->plugin_slug = 'oriel-wp';
		$this->version     = '8.8.8';
		$this->_init();
	}

	/**
	 * Hooks and crypto seed initializer
	 */
	private function _init() {
		global $cache, $wpsettings;

		$is_amp = OrielUtil::is_amp_endpoint();
		if ( $is_amp ) {
			return;
		}

		$this->oriel = Oriel\Oriel::instance();
		$this->oriel->init( $wpsettings, $cache );

		$disable_param = $this->oriel->get_settings()['disable_oriel_param'];

		$disable_oriel_exists = $disable_param && isset( $_GET[ $disable_param ] );

		if ( ! is_admin() && ! in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) && ! $disable_oriel_exists ) {
			/*
			 * Start output buffering at a very low priority to have access to entire HTML page
			 * e.g. output from other plugins that use add_action('wp_loaded') to add content
			 */
			$priority = defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX;
			add_action( 'wp_loaded', array( $this, 'hook_ob_start' ), $priority );

			/*
			 * Flush output buffering at a very high priority
			 */
			add_action( 'shutdown', array( $this, 'hook_ob_end' ), PHP_INT_MAX );
		} elseif ( is_admin() ) {
			// Register plugin action links
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_action_links' ) );

			// Register cleanup hook
			register_deactivation_hook( __FILE__, array( $this, 'deactivate_plugin_hook' ) );

			// Register activation hook
			register_activation_hook( __FILE__, array( $this, 'activate_plugin_hook' ) );

			// Add notices hook
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
	}

	/**
	 * Notifies admin with current status
	 */
	public function admin_notices() {
		global $hook_suffix, $cache;

		if ( 'plugins.php' === $hook_suffix ) {
			$domain = Oriel\API::get_domain_data();
			if ( ! $domain || $domain['is_stale'] ) {
				?>
				<style>
					.oriel-notice {
						padding: 10px;
						background-color: #6BC2B9;
						border: 0;
						color: #FFF;
						font-size: 16px;
						border-radius: 5px;
					}

					.oriel-action:visited, .oriel-action:link {
						display: inline-block;
						padding: 15px 25px;
						margin: 0 20px 0 0;
						color: #333;
						background-color: #FED47A;
						border: 3px solid #FFF;
						text-decoration: none;
						font-size: 16px;
						font-weight: bold;
						border-radius: 5px;
					}

					.oriel-action:hover {
						background-color: #feb963;
						border-color: #EEE;
					}
				</style>
				<div class="notice oriel-notice">
					<a href="<?php echo esc_url( admin_url( 'options-general.php?page=oriel-settings-admin' ) ); ?>"
					class="oriel-action"> Activate your Oriel account</a>
					Just one more step to get Oriel going on your website!
				</div>
				<?php
			}
		}

		if ( ! function_exists( 'curl_init' ) && ! function_exists( 'wp_remote_get' ) ) {
			// Notify admin if curl is not installed
			$msg = __( 'PHP cURL extension not installed! Please make sure you have cURL installed in order for Oriel Plugin to work!', 'sample-text-domain' );
			printf( '<div class="notice notice-error"><p>%1$s</p></div>', esc_html( $msg ) );
		}
	}

	/**
	 * Adds settings shortcut to Oriel in plugins list
	 *
	 * @param  string[] $links Oriel links already available
	 * @return string[] The Oriel links with added settings shortcut
	 */
	public function add_action_links( $links ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=oriel-settings-admin' ) . '">Settings</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}


	/**
	 * Hook to replace tags
	 */
	public function hook_ob_start() {
		ob_start( array( $this, 'process_request' ) );
	}

	/**
	 * Hook to flush output buffer
	 */
	public function hook_ob_end() {
		if ( ob_get_length() ) {
			ob_end_flush();
		}
	}

	/**
	 * Processes requests
	 *
	 * @param  string $html rendered HTML
	 * @return string Processed HTML
	 */
	public function process_request( $html ) {
		return $this->oriel->process_request( $html );
	}

	/**
	 * Cleanup method ran at plugin deactivation, clears cache
	 */
	public function deactivate_plugin_hook() {
		$this->oriel->get_cache()->erase();
	}

	/**
	 * Method ran at plugin activation, cleans cache for a fresh run
	 */
	public function activate_plugin_hook() {
		$this->oriel->get_cache()->erase();
	}
}

$orielwp = new OrielWP();

?>
