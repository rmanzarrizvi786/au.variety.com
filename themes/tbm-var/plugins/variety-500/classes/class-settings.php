<?php
/**
 * Settings
 *
 * The admin settings page.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

namespace Variety\Plugins\Variety_500;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Settngs
 *
 * Creates the settings page and available options.
 *
 * @since 1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Settings {

	use Singleton;

	/**
	 * Settings Group
	 *
	 * @since 1.0
	 */
	const SETTINGS_GROUP = 'variety-500';

	/**
	 * Nonce
	 *
	 * @since 1.0
	 * @var string The nonce value name for the Ajax request.
	 */
	const NONCE = 'sortable-autocomplete';

	/**
	 * Class constructor.
	 *
	 * Initializes the plugin and gets things started on the `init` action.
	 *
	 * @since 1.0
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 1000 );
		add_action( 'wp_ajax_v500-profile-query', array( $this, 'select_profile' ) );
		$this->register_nav();

		// Check if we should clear the stats cache.
		if (
			! empty( $_GET['clear-cache'] ) && // WPCS: Input var okay.
			! empty( $_GET['_wpnonce'] ) && // WPCS: Input var okay.
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'clear-cache' ) // WPCS: Input var okay.
		) {
			Stats::get_instance()->invalidate_stats_cache();
		}
	}

	/**
	 * Register Settings
	 *
	 * Registers the options we want to use.
	 *
	 * @since 1.0
	 */
	public function register_settings() {
		register_setting( self::SETTINGS_GROUP, 'variety_500_year' );
		register_setting( self::SETTINGS_GROUP, 'variety_500_presented_by' );
		register_setting( self::SETTINGS_GROUP, 'variety_500_sponsor_link' );
		register_setting( self::SETTINGS_GROUP, 'variety_500_sponsor_logo' );
		register_setting( self::SETTINGS_GROUP, 'variety_500_sponsor_hero_logo' );
		register_setting( self::SETTINGS_GROUP, 'variety_500_sponsor_pixel' );
		register_setting( self::SETTINGS_GROUP, 'variety_500_spotlight_profiles' );
		register_setting( self::SETTINGS_GROUP, 'variety_500_instagram_profiles' );
		register_setting( self::SETTINGS_GROUP, 'variety_500_vi_cta_link' );
		register_setting( self::SETTINGS_GROUP, 'variety_500_home_video_id' );
		register_setting( self::SETTINGS_GROUP, 'variety_500_interviews_term' );
	}

	/**
	 * Register Settings Page
	 *
	 * Adds the admin menu and registers the settings page.
	 *
	 * @since 1.0
	 */
	public function register_settings_page() {
		add_options_page( 'Variety 500 Settings', 'Variety 500', 'manage_options', 'variety-500', array( $this, 'render_settings_page' ) );
	}

	/**
	 * Render Settings Page
	 *
	 * Loads the settings page template.
	 *
	 * @since 1.0
	 */
	public function render_settings_page() {
		include untrailingslashit( VARIETY_500_ROOT ) . '/templates/admin/settings.php';
	}

	/**
	 * Select Profile
	 *
	 * Ajax action implementing Autocomplete
	 * for the Settings page Spotlight profile
	 * selection.
	 *
	 * Utilizes the prepackaged WP
	 * Autocomplete JS, and functions
	 * similarly to the WP "add existing user"
	 * interface.
	 *
	 * @since 1.0
	 */
	public function select_profile() {
		check_ajax_referer( self::NONCE );
		$return = array();

		if ( ! empty( sanitize_text_field( wp_unslash( $_REQUEST['term'] ) ) ) ) { // WPCS Input var okay.

			$variety_500_year = get_option( 'variety_500_year', date( 'Y' ) );

			$query = new \WP_Query( array(
				'post_type'              => 'hollywood_exec',
				's'                      => $term = sanitize_text_field( wp_unslash( $_REQUEST['term'] ) ), // WPCS Input var okay.
				'posts_per_page'         => 20,
				'tax_query'              => array(
					array(
						'taxonomy'         => 'vy500_year',
						'terms'            => $variety_500_year,
						'field'            => 'slug',
						'include_children' => false,
					),
				),
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
			) );

			foreach ( $query->posts as $post ) {
				$return[] = array(
					'label' => $post->post_title,
					'value' => $post->post_title,
					'id'    => $post->ID,
				);
			}
		}

		wp_die( wp_json_encode( $return ) );
	}

	/**
	 * Enqueue Admin Assets
	 *
	 * Enqueue the Variety 500 Admin stylesheets and scripts.
	 *
	 * @see pmc_variety_scripts_and_styles()
	 *
	 * @since 1.0
	 * @param string $hook The admin page name.
	 */
	public static function admin_enqueue_assets( $hook ) {
		if ( 'settings_page_variety-500' !== $hook ) {
			return;
		}

		wp_register_script( 'v500-settings-js', untrailingslashit( VARIETY_500_PLUGIN_URL ) . '/assets/js/vendor/admin.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-autocomplete', 'underscore' ) );
		wp_localize_script( 'v500-settings-js', 'v500js', array(
				'nonce' => wp_create_nonce( self::NONCE ),
			)
		);
		wp_enqueue_script( 'v500-settings-js' );
		wp_register_style( 'v500-settings-css', untrailingslashit( VARIETY_500_PLUGIN_URL ) . '/assets/css/admin.css' );
		wp_enqueue_style( 'v500-settings-css' );
	}

	/**
	 * Register nav menus required for Variety 500.
	 *
	 * @return void
	 */
	public function register_nav() {

		$menus = [
			'pmc_variety_500_footer' => __( 'Variety 500 Footer', 'pmc-variety' ),
		];

		register_nav_menus( $menus );
	}
}

// EOF.
