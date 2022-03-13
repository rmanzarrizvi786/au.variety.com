<?php

namespace PMC\Social_Share_Bar;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

class Admin {

	use Singleton;

	const NONCE_KEY = 'pmc-social-share-bar';
	const SHARE_ICONS = 'pmc_share_icons_list';
	const PRIMARY_ICONS = 'pmc_primary_share_icons_list';
	const SECONDARY_ICONS = 'pmc_secondary_share_icons_list';
	const PRIMARY = 'primary';
	const SECONDARY = 'secondary';

	private $_primary_default_count = 4;
	private $_min_count = 3;
	private $_max_count = 4;

	private $_api;

	private $_config;

	/**
	 * Set up hooks and filters in init
	 *
	 * @since 2016-02-10
	 * @version 2016-02-10 Archana Mandhare - PMCVIP-815
	 *
	 */
	protected function __construct() {

		$this->_api    = API::get_instance();
		$this->_config = Config::get_instance();
		$bitly_options = get_option( 'bitly_settings' );

		// this filter need to add before init fire so cheezcap can see the filter during init.
		add_filter( 'pmc_global_cheezcap_options', array(
			$this,
			"filter_pmc_social_share_bar_global_cheezcap_options"
		) );

		add_action( 'admin_menu', array( $this, 'admin_settings_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_assets' ) );
		add_action( 'wp_ajax_save_order', array( $this, 'save_order' ) );
		add_action( 'wp_ajax_reset_order', array( $this, 'reset_order' ) );
		add_action( 'wp_ajax_get_order', array( $this, 'ajax_get_order' ) );

		if ( isset( $bitly_options['api_login'] ) && isset( $bitly_options['api_key'] ) ) {
			add_action( 'save_post', array( $this, 'generate_shortlinks' ) );
		}

	}

	/*
	 * Create the Cheezcap settings in the admin to toggle the PMC Social Share Bar and the old share
	 * @since 2016-03-08
	 * @version 2016-03-08 Archana Mandhare PMCVIP-815
	 *
	 * @param $cheezcap_groups array
	 * @return array
	 */
	public function filter_pmc_social_share_bar_global_cheezcap_options( $cheezcap_options = array() ) {

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags( __( 'PMC Social Share Bar', 'pmc-social-share-bar' ), true ),
			wp_strip_all_tags( __( 'When Enabled, The Social buttons will be replaced with the PMC Social Share bar', 'pmc-social-share-bar' ), true ),
			'pmc_social_share_bar_enabled',
			array( 'disabled', 'enabled' ),
			0, // 1sts option => Disabled
			array( wp_strip_all_tags( __( 'Disabled', 'pmc-social-share-bar' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-social-share-bar' ), true ) )
		);

		return $cheezcap_options;

	}

	/**
	 * Set up Assets
	 *
	 * @since 2016-02-10
	 * @version 2016-02-10 Archana Mandhare - PMCVIP-815
	 *
	 * @param $hook string
	 *
	 */
	public function load_admin_assets( $hook = '' ) {

		if ( strtolower( $hook ) !== 'settings_page_pmc-social-bar' ) {
			//not our admin page, bail out
			return;
		}

		wp_enqueue_style( 'pmc-social-share-bar-admin-css', plugins_url( 'pmc-social-share-bar/_build/css/admin.css', PMC_SOCIAL_SHARE_BAR_ROOT ) );

		wp_enqueue_script( 'pmc-social-share-bar-admin-js', plugins_url( 'pmc-social-share-bar/_build/js/admin.js', PMC_SOCIAL_SHARE_BAR_ROOT ), array(
			'jquery',
			'jquery-ui-sortable'
		), '1.0', true );

		wp_localize_script( 'pmc-social-share-bar-admin-js', 'pmc_social_share_bar_options', array(
			'url'                        => admin_url( 'admin-ajax.php' ),
			'pmc_social_share_bar_nonce' => wp_create_nonce( self::NONCE_KEY ),
			'min_primary_count'          => min( $this->_min_count, $this->get_primary_icons_list_count() ),
			'max_primary_count'          => max( $this->_max_count, $this->get_primary_icons_list_count() ),
			'default_primary_count'      => $this->_primary_default_count,
		) );
	}

	/**
	 *
	 * Get the number of icons in primary list
	 *
	 * @since 2016-02-11
	 * @version 2016-02-11 Archana Mandhare - PMCVIP-815
	 *
	 * @return int
	 */
	public function get_primary_icons_list_count() {

		return (int) apply_filters( 'pmc_social_share_bar_primary_list_count', $this->_primary_default_count );

	}

	/**
	 * Register the settings page
	 *
	 * @since 2016-02-10
	 * @version 2016-02-10 Archana Mandhare - PMCVIP-815
	 *
	 */
	public function admin_settings_menu() {
		add_options_page(
			wp_strip_all_tags( __( 'Social Bar', 'pmc-social-share-bar' ), true ),       // Page Title
			wp_strip_all_tags( __( 'Social Bar', 'pmc-social-share-bar' ), true ),       // Menu Title
			'manage_options',   // Capability
			'pmc-social-bar',       // Menu Slug
			array( $this, 'render_admin_page' )  // Callback function
		);
	}

	/**
	 * Render the admin page template
	 *
	 * @since 2016-02-10
	 * @version 2016-02-10 Archana Mandhare - PMCVIP-815
	 *
	 */
	public function render_admin_page() {

		$special_icons = array();

		$primary_icons_list = $this->_api->get_share_icons( Admin::PRIMARY );

		$secondary_icons_list = $this->_api->get_share_icons( Admin::SECONDARY );

		$special_icons_list = apply_filters( 'pmc_lob_special_icon', array() );

		if ( ! empty( $special_icons_list ) ) {

			foreach ( $special_icons_list as $icon_id ) {
				$icon                      = $this->_config->get_social_share_icons_object( $icon_id );
				$special_icons[ $icon_id ] = $icon;
			}
			$primary_icons_list = array_diff( $primary_icons_list, $special_icons_list );
		}

		foreach ( $primary_icons_list as $icon_id ) {
			$icon                      = $this->_config->get_social_share_icons_object( $icon_id );
			$primary_icons[ $icon_id ] = $icon;
		}

		foreach ( $secondary_icons_list as $icon_id ) {
			$icon                        = $this->_config->get_social_share_icons_object( $icon_id );
			$secondary_icons[ $icon_id ] = $icon;
		}

		/**
		 * Get post type list.
		 * To create dropdown of post type.
		 */
		$post_types = get_post_types( array( 'public' => true ) );

		if ( empty( $primary_icons ) || empty( $secondary_icons ) ) {
			throw new \Exception( esc_html__( 'The icons are not set in the theme. Please set the icons using the filter "pmc_default_share_icons_list" ', 'pmc-social-share-bar' ) );
		}

		echo PMC::render_template( PMC_SOCIAL_SHARE_BAR_ROOT . '/_build/svg/pmc-social-icons.svg', array());
		echo PMC::render_template( PMC_SOCIAL_SHARE_BAR_ROOT . '/templates/admin.php', array(
			'social_share_icons'      => $primary_icons,
			'more_social_share_icons' => $secondary_icons,
			'lob_special_share_icons' => $special_icons,
			'post_types'              => $post_types,
		) );

	}

	/**
	 * AJAX Call back to get icon order of post type or default configuration.
	 *
	 * @since	2017-03-10
	 * @version 2017-03-10 Dhaval Parekh CDWE-247
	 */
	public function ajax_get_order() {
		check_ajax_referer( self::NONCE_KEY, 'pmc_social_share_bar_nonce' );

		// Validate post type.
		$post_type = ( ! empty( $_POST['post_type'] ) ) ? sanitize_title( $_POST['post_type'] ) : '';

		$post_types = get_post_types( array( 'public' => true ) );

		if ( ! in_array( $post_type, $post_types, true ) ) {
			$post_type = '';
		}

		// Get data.
		$icon_order = array();
		$icon_order['primary'] = $this->_api->get_share_icons( Admin::PRIMARY, $post_type );
		$icon_order['secondary'] = $this->_api->get_share_icons( Admin::SECONDARY, $post_type );
		wp_send_json_success( $icon_order );
	}

	/**
	 * To get meta/cache key according to given post type.
	 *
	 * @since	2017-03-10
	 * @version 2017-03-10 Dhaval Parekh CDWE-247
	 */
	public function get_keys( $post_type = '' ) {
		/**
		 * Default meta/cache keys
		 */
		$meta_keys = array(
			'primary'   => self::PRIMARY_ICONS,
			'secondary'	=> self::SECONDARY_ICONS,
		);
		$cache_keys = array(
			'primary'   => self::PRIMARY,
			'secondary'	=> self::SECONDARY,
		);

		/**
		 * If custom post type given then.
		 */
		$post_type = sanitize_title( $post_type );
		if ( ! empty( $post_type ) && 'default' !== $post_type ) {
			/**
			 * If post type is provided,
			 * then check if requested post type is valid or not.
			 */
			$post_types = get_post_types( array( 'public' => true ) );

			if ( in_array( $post_type, $post_types, true ) ) {

				$meta_keys = array(
					'primary'   => self::PRIMARY_ICONS . $post_type,
					'secondary'	=> self::SECONDARY_ICONS . $post_type,
				);
				$cache_keys = array(
					'primary'   => self::PRIMARY . $post_type,
					'secondary'	=> self::SECONDARY . $post_type,
				);
			}
		}

		$keys = array(
			'meta'  => $meta_keys,
			'cache'	=> $cache_keys,
		);

		return $keys;
	}

	/**
	 * Ajax call back to save the order of the icons in DB in pmc option
	 *
	 * @since 2016-02-10
	 * @version 2016-02-10 Archana Mandhare - PMCVIP-815
	 *
	 */
	public function save_order() {

		check_ajax_referer( self::NONCE_KEY, 'pmc_social_share_bar_nonce' );
		$post_type = false;
		$special_icons = apply_filters( 'pmc_lob_special_icon', array() );

		/**
		 * If custom post type given then.
		 * store order for that post type.
		 */
		if ( ! empty( $_POST['post_type'] ) && 'default' !== $_POST['post_type'] ) {
			$post_type = sanitize_title( $_POST['post_type'] );
			/**
			 * If we update icon order for specific post type.
			 * then check if requested post type is valid or not.
			 */
			$post_types = get_post_types( array( 'public' => true ) );

			if ( ! in_array( $post_type, $post_types, true ) ) {
				$post_type = '';
			}
		}

		$keys = $this->get_keys( $post_type );
		$meta_keys = $keys['meta'];
		$cache_keys = $keys['cache'];

		// Save the primary icons list to DB in pmc options
		if ( ! empty( $_POST['primary_icons'] ) && is_array( $_POST['primary_icons'] ) ) {

			$primary_icons = array_map( 'sanitize_text_field', $_POST['primary_icons'] );

			if ( ! empty( $special_icons ) ) {
				$primary_icons = array_diff( $primary_icons, $special_icons );

				$primary_icons = array_merge( $primary_icons, $special_icons );
			}

			pmc_update_option( $meta_keys['primary'], $primary_icons );
			$this->_api->update_cache( $primary_icons, $cache_keys['primary'] );
		}

		// Save the secondary icons list to DB in pmc options
		if ( ! empty( $_POST['secondary_icons'] ) && is_array( $_POST['secondary_icons'] ) ) {
			$secondary_icons = array_map( 'sanitize_text_field', $_POST['secondary_icons'] );
			if ( ! empty( $special_icons ) ) {
				$secondary_icons = array_diff( $secondary_icons, $special_icons );
			}
			pmc_update_option( $meta_keys['secondary'], $secondary_icons );
			$this->_api->update_cache( $secondary_icons, $cache_keys['secondary'] );
		}

		// Output response
		wp_send_json( array(
			'success' => true,
			'primary'   => $primary_icons,
			'secondary' => $secondary_icons
		) );

	}

	/**
	 * Ajax call back to reset the position of icons to default set by LOB via filter
	 *
	 * @since 2016-02-10
	 * @version 2016-02-10 Archana Mandhare - PMCVIP-815
	 *
	 */
	public function reset_order() {

		check_ajax_referer( self::NONCE_KEY, 'pmc_social_share_bar_nonce' );

		$icons_count = $this->get_primary_icons_list_count();

		$lob_icons = array_keys( $this->_config->get_social_share_icons_object() );

		$primary_list   = array_slice( $lob_icons, 0, $icons_count );
		$secondary_list = array_slice( $lob_icons, $icons_count, count( $lob_icons ) - 1 );

		$primary_icons = apply_filters( 'pmc_default_primary_share_icons_list', $primary_list );

		/**
		 * If custom post type given then.
		 * store order for that post type.
		 */
		$post_type = false;
		if ( ! empty( $_POST['post_type'] ) && 'default' !== $_POST['post_type'] ) {
			$post_type = sanitize_title( $_POST['post_type'] );
		}

		$keys = $this->get_keys( $post_type );
		$meta_keys = $keys['meta'];
		$cache_keys = $keys['cache'];

		if ( ! empty( $primary_icons ) ) {
			pmc_update_option( $meta_keys['primary'], $primary_icons );
			$this->_api->update_cache( $primary_icons, $cache_keys['primary'] );
		}

		$secondary_icons = apply_filters( 'pmc_default_secondary_share_icons_list', $secondary_list );
		if ( ! empty( $secondary_icons ) ) {
			pmc_update_option( $meta_keys['secondary'], $secondary_icons );
			$this->_api->update_cache( $secondary_icons, $cache_keys['secondary'] );
		}

		if ( ! empty( $primary_icons ) && ! empty( $secondary_icons ) ) {
			// Output response
			wp_send_json_success( array(
				'primary'   => $primary_icons,
				'secondary' => $secondary_icons
			) );

		} else {
			// Output response
			wp_send_json_error();
		}

		exit();
	}

	/**
	 * Social Share links needs to be shortlinks, the long URLs are not encouraged for
	 * shared content.
	 *
	 * Generates bitly shortlinks for each positions [ 'top', 'bottom' ] for utm tracking query
	 * vars.
	 *
	 * Stores these generated shortlinks as array in post_meta,
	 * [
	 *  'top' => [
	 *          $social_network => $short_url,
	 *      ],
	 * 'bottom' => [
	 *          $social_network => $short_url,
	 *      ],
	 * ]
	 *
	 * @since 2018-05-15 PMCEED-456
	 *
	 * @author Divyaraj Masani <divyaraj.masani@rtcamp.com>
	 */
	public function generate_shortlinks() {

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		$post = get_post();

		// Bail early if no Bitly available, or post.
		if ( empty( $post ) || ! class_exists( 'Bitly' ) ) {
			return;
		}

		$bitly     = new \Bitly();
		$post_type = $post->post_type;
		$positions = [ 'bottom', 'top' ];

		// This have different schemes and handled differently.
		$ignore_links_for = [ 'WhatsApp', 'Email', 'Print', 'Talk' ];

		$social_shortlinks      = get_post_meta( $post->ID, 'social_shortlinks' );
		$social_shortlinks_back = $social_shortlinks;

		$primary_icons   = $this->_api->get_share_icons( self::PRIMARY, $post_type );
		$secondary_icons = $this->_api->get_share_icons( self::SECONDARY, $post_type );

		$lob_icons = array_merge( $primary_icons, $secondary_icons );

		foreach ( $lob_icons as $icon_id ) {

			$icon = $this->_config->get_social_share_icons_object( $icon_id );

			// @codingStandardsIgnoreLine $ignore_links_for is predefined array,
			if ( in_array( $icon->name, $ignore_links_for, true ) ) {
				continue;
			}

			foreach ( $positions as $position ) {

				if ( empty( $social_shortlinks[ $position ][ $icon_id ] ) ) {

					// Query params for tracking.
					$arr = array(
						'utm_medium'   => 'social',
						'utm_source'   => (string) $icon_id,
						'utm_campaign' => 'social_bar',
						'utm_content'  => (string) $position, // Location on page
						'utm_id'       => $post->ID,
					);

					// Sending regular URL as the 'shortlink_for_url' encodes the url internally.
					// Bitly expects the URL to be encoded once.
					$url = get_the_permalink( $post->ID ) . '#' . urldecode( http_build_query( $arr ) );

					$short_url = $bitly->shortlink_for_url( $url );

					if ( ! empty( $short_url ) ) {
						$social_shortlinks[ $position ][ $icon_id ] = $short_url;
					}
				}
			}
		}

		// Make sure we have any new entries.
		$diff = array_diff( (array) $social_shortlinks, (array) $social_shortlinks_back );

		if ( ! empty( $social_shortlinks ) && ! empty( $diff ) ) {
			update_post_meta( $post->ID, 'social_shortlinks', $social_shortlinks );
		}
	}

} // end class

//EOF
