<?php
/**
 * Class contains all functions related to Scorecard Settings.
 *
 * CDWE-477 -- Copied from pmc-variety-2014 theme.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-08-21
 */

use \PMC\Global_Functions\Traits\Singleton;

class Variety_Scorecard_Settings {

	use Singleton;

	/**
	 * Contains all options settings value which is set in options.
	 *
	 * @var array|bool Contains settings value, default is false.
	 */
	protected $_options = false;

	/**
	 * Class Initialization.
	 */
	protected function __construct() {

		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Include settings.
	 */
	public function admin_init() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->register_settings();
	}

	/**
	 * Add menu in Admin area.
	 */
	public function admin_menu() {

		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_options_page(
			'Pilots Scorecard',
			'Pilots Scorecard',
			'manage_options',
			'scorecard-settings',
			array(
				$this,
				'admin_options_page',
			)
		);
	}

	/**
	 * Setup all settings fields.
	 *
	 * @return array List of settings fields.
	 */
	protected function _get_setting_definitions() {

		$settings = array(
			array(
				'url_get_records',
				'Get Records URL',
				array( $this, 'field_url_get_records' ),
				'variety_scorecard_settings',
				'variety_scorecard_service',
				array(),
			),
			array(
				'url_get_networks',
				'Get Networks URL:',
				array( $this, 'field_url_get_networks' ),
				'variety_scorecard_settings',
				'variety_scorecard_service',
				array(),
			),
			array(
				'url_get_genre',
				'Get Genre URL:',
				array( $this, 'field_url_get_genre' ),
				'variety_scorecard_settings',
				'variety_scorecard_service',
				array(),
			),
			array(
				'url_get_status',
				'Get Status URL:',
				array( $this, 'field_url_get_status' ),
				'variety_scorecard_settings',
				'variety_scorecard_service',
				array(),
			),
		);

		return $settings;

	} // end function _get_setting_definitions

	/**
	 * Register all settings.
	 */
	public function register_settings() {

		$settings_fields = $this->_get_setting_definitions();
		$valid_config    = true;
		$options         = $this->get_option();

		if ( empty( $options ) ) {
			$options      = array();
			$valid_config = false;
		}

		register_setting(
			'variety_scorecard_settings',
			'variety_scorecard_settings',
			array(
				'sanitize_callback' => array( $this, 'sanitize_admin_options' ),
			)
		);

		add_settings_section( 'variety_scorecard_service', 'Pilots Scorecard Service Endpoint URLs', '__return_false', 'variety_scorecard_settings' );

		foreach ( $settings_fields as $field ) {
			list( $id, $title, $callback, $page, $section, $args ) = $field;
			add_settings_field( $id, $title, $callback, $page, $section, $args );
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @param string $hook Name of the current page.
	 */
	public function enqueue_scripts( $hook ) {

		// Enqueue script only on scorecard page.
		if ( 'settings_page_scorecard-settings' === $hook ) {
			wp_enqueue_style( 'scorecard-style', VARIETY_THEME_URL . '/plugins/variety-scorecard/css/style.css' );
		}
	}

	/**
	 * Field for get records.
	 */
	public function field_url_get_records() {

		printf(
			'<input name="variety_scorecard_settings[url_get_records]" id="url_get_records" type="text" value="%s">',
			esc_attr( $this->get_option( 'url_get_records' ) )
		);
	}

	/**
	 * Field for get networks.
	 */
	public function field_url_get_networks() {

		printf(
			'<input name="variety_scorecard_settings[url_get_networks]" id="url_get_networks" type="text" value="%s">',
			esc_attr( $this->get_option( 'url_get_networks' ) )
		);
	}

	/**
	 * Field for get genre.
	 */
	public function field_url_get_genre() {

		printf(
			'<input name="variety_scorecard_settings[url_get_genre]" id="url_get_genre" type="text" value="%s">',
			esc_attr( $this->get_option( 'url_get_genre' ) )
		);
	}

	/**
	 * Field for get status.
	 */
	public function field_url_get_status() {

		printf(
			'<input name="variety_scorecard_settings[url_get_status]" id="url_get_status" type="text" value="%s">',
			esc_attr( $this->get_option( 'url_get_status' ) )
		);
	}

	/**
	 * Validate values added in fields.
	 *
	 * @param array $options List of fields.
	 *
	 * @return array Returns validated values of all fields.
	 */
	public function sanitize_admin_options( $options ) {

		// we don't want someone accidentially enter these values.
		$exclude_domains = array( 'www.variety.com', 'variety.com' );

		// only allow http & https.
		$protocols = array( 'http', 'https' );

		foreach ( $options as $key => $value ) {
			$value  = esc_url_raw( $value, $protocols );
			$domain = wp_parse_url( $value, PHP_URL_HOST );

			if ( in_array( $domain, $exclude_domains, true ) ) {
				$value = '';
			}

			$options[ $key ] = $value;
		}

		return $options;
	}

	/**
	 * Used to get one or all plugin options.
	 *
	 * @param string      $option  (optional) Name of option to retrieve. Empty to return all options.
	 * @param string|bool $default (optional) Default value of the option if settings is not set, default is false.
	 *
	 * @return array of options, or option value.
	 */
	public function get_option( $option = null, $default = false ) {

		if ( ! is_array( $this->_options ) ) {
			$this->_options = get_option( 'variety_scorecard_settings' );
		}

		if ( isset( $option ) ) {
			if ( isset( $this->_options[ $option ] ) ) {
				return $this->_options[ $option ];
			} else {
				return $default;
			}
		} else {
			return $this->_options;
		}
	}

	/**
	 * Adds settings page on admin side.
	 */
	public function admin_options_page() {

		$settings_updated = filter_input( INPUT_GET, 'settings-updated' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		/**
		 * @since 2017-09-01 Milind More CDWE-499
		 */
		echo \PMC::render_template( CHILD_THEME_PATH . '/plugins/variety-scorecard/templates/scorecard-settings.php',
			array(
				'settings_updated' => $settings_updated,
			)
		);

	}

}

//EOF
