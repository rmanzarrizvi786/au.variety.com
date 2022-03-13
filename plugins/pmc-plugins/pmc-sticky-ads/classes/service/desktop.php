<?php
/**
 * Desktop service class for PMC Sticky Ads plugin.
 * It adds sticky ads on desktop devices
 *
 * @author Brian Van <Brian.VanNieuwenhoven@ey.com>
 * @since 2017-06-13
 */

namespace PMC\Sticky_Ads\Service;


use \PMC;
use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Sticky_Ads\Config;

class Desktop {

	use Singleton;

	const ID = 'pmc-sticky-ads-desktop';

	/**
	 * @var \PMC\Sticky_Ads\Config
	 */
	protected $_config;

	/**
	 * @var string Plugin's asset dir URL
	 */
	protected $_assets_url;

	/**
	 * @var array An array of ad slot IDs for specific positions.
	 */
	protected $_ad_slots = array(

		'bottom' => array(

			'key'  => 'desktop-bottom-sticky-ad',
			'name' => 'Desktop Bottom Sticky Ad',

		),

	);

	/**
	 * @var array An array of post types on which sticky ads are to be shown on desktop by default
	 */
	protected $_default_types_to_show_on = array(
		'post',
	);

	/**
	 * Class initialization method
	 *
	 * @return void
	 */
	protected function __construct() {

		$this->_config = Config::get_instance();

		$this->_assets_url = plugins_url( 'assets', sprintf( '%s/assets', untrailingslashit( PMC_STICKY_ADS_ROOT ) ) );

		$this->_setup_hooks();

	}

	/**
	 * Method which sets up listeners to WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/*
		 * Actions
		 */
		add_action( 'wp_footer', array( $this, 'render_bottom_ad' ), 4 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_stuff' ) );
		add_action( 'pmc_sticky_ads_desktop_close_button', array( $this, 'close_button' ) );

		/*
		 * Filters
		 */
		add_filter( 'pmc_adm_locations', array( $this, 'add_ad_locations' ) );

	}

	/**
	 * Conditional method to determine whether bottom ad should be displayed or not
	 *
	 * @return boolean
	 */
	protected function _show_bottom_ad() {

		$post_types = array_filter(
			array_unique(
				(array) apply_filters( 'pmc-sticky-ads-desktop-post-types', $this->_default_types_to_show_on )
			)
		);

		$is_enabled_for_home_page = $this->_config->get_single_for_service( 'desktop', 'enable-on-home-page', false );
		$is_enabled_for_landing_pages = $this->_config->get_single_for_service( 'desktop', 'enable-on-landing-pages', false );

		if (
			! is_admin() && (
				( is_single() && in_array( get_post_type(), $post_types ) )
				|| ( $is_enabled_for_landing_pages === true && is_archive() )
				|| ( $is_enabled_for_home_page === true && is_home() )
			)
		) {
			return true;
		}

		return false;

	}

	/**
	 * Method to fetch ad slot slug for a specific position
	 *
	 * @param string $position Position for which ad slot slug is to be fetched
	 * @return string Returns slug of the ad slot for the queried position if both position & ad slot exist else empty string
	 */
	protected function _get_ad_slot( $position = '' ) {

		if ( empty( $position ) || ! is_string( $position ) || empty( $this->_ad_slots[ $position ] ) ) {
			return '';
		}

		switch ( $position ) {

			case 'bottom':
				return $this->_ad_slots[ $position ]['key'];

		}

		return '';

	}

	/**
	 * Called on 'pmc_adm_locations' filter, this method registers ad slots with PMC Ad Manager
	 *
	 * @param  array $locations An array of registered ad slots.
	 * @return array An array of registered ad slots
	 */
	public function add_ad_locations( $locations = array() ) {

		if ( empty( $this->_ad_slots ) || ! is_array( $this->_ad_slots ) ) {
			return $locations;
		}

		foreach ( $this->_ad_slots as $ad_slot ) {

			$locations[ $ad_slot['key'] ] = [
				'title'     => $ad_slot['name'],
				'providers' => [ 'boomerang', 'google-publisher' ],
			];

		}

		return $locations;

	}

	/**
	 * Called by 'wp_enqueue_scripts' action, this method enqueues CSS/JS assets on the page
	 *
	 * @return void
	 */
	public function enqueue_stuff() {

		global $post;

		if ( ! $this->_show_bottom_ad() ) {
			return;
		}

		$post_types = array_filter(
			array_unique(
				(array) apply_filters( 'pmc-sticky-ads-desktop-post-types-onload', array() )
			)
		);

		$onload = false;

		if ( is_single() && is_a( $post, 'WP_Post' ) && in_array( $post->post_type, $post_types ) ) {
			$onload = true;
		}

		$js_extension = '.js';

		if ( \PMC::is_production() ) {
			$js_extension = '.min.js';
		}

		wp_enqueue_script( 'waypoints', pmc_global_functions_url( sprintf( '/js/waypoints%s', $js_extension ) ), array( 'jquery' ), '1.1.7', true );

		wp_enqueue_style( sprintf( '%s-css', self::ID ), sprintf( '%s/css/desktop.css', $this->_assets_url ) );

		wp_enqueue_script( sprintf( '%s-js', self::ID ), sprintf( '%s/js/desktop%s', $this->_assets_url, $js_extension ), array( 'jquery', 'waypoints' ), '1.0', true );

		wp_localize_script( sprintf( '%s-js', self::ID ), 'pmc_sticky_ads_desktop_config', array(

			'leaderboard' => $this->_config->get_single_for_service( 'desktop', 'leaderboard', '' ),
			'onload'      => (bool) $onload,

		) );

	}

	/**
	 * Called on 'wp_footer', this method renders the ad slot for bottom position
	 *
	 * @return void
	 */
	public function render_bottom_ad() {

		if ( ! $this->_show_bottom_ad() ) {
			return;
		}

		echo PMC::render_template( sprintf( '%s/templates/service/desktop/bottom-ad.php', untrailingslashit( PMC_STICKY_ADS_ROOT ) ), array(

			'ad_slot'            => $this->_get_ad_slot( 'bottom' ),

		) );

	}

	/**
	 * Close button for sticky ad.
	 *
	 * @return void
	 */
	public function close_button() {
		echo PMC::render_template( sprintf( '%s/templates/service/desktop/close-button.php', untrailingslashit( PMC_STICKY_ADS_ROOT ) ) );
	}

}	//end of class


//EOF
