<?php

namespace PMC\Social_Share_Bar;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

class Frontend {

	use Singleton;

	private $_api;

	private $_config;

	static $called = 0;

	/**
	 *
	 * Setup hooks on Init
	 *
	 * @since 2016-02-11
	 * @version 2016-02-11 Archana Mandhare - PMCVIP-815
	 *
	 */
	protected function __construct() {
		$this->_api    = API::get_instance();
		$this->_config = Config::get_instance();

		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_assets' ) );
		add_filter( 'kses_allowed_protocols', [ $this, 'allowed_url_protocols' ] );
	}

	/**
	 *
	 * Load the _build required on the frontend
	 *
	 * @since 2016-02-11
	 * @version 2016-02-11 Archana Mandhare - PMCVIP-815
	 * @version 2016-10-24 Brandon Camenisch - PPT-7055:
	 * - Adding is_singular check since we don't want this to load on the homepage.
	 *
	 */
	public function load_frontend_assets() {
		wp_enqueue_style( 'pmc-social-share-bar-common-css', plugins_url( 'pmc-social-share-bar/_build/css/frontend.css', PMC_SOCIAL_SHARE_BAR_ROOT ) );
		wp_enqueue_script( 'pmc-social-share-bar-frontend-js', plugins_url( 'pmc-social-share-bar/_build/js/frontend.js', PMC_SOCIAL_SHARE_BAR_ROOT ), array( 'jquery' ), '1.0', true );
		wp_enqueue_script( 'pmc-social-share-bar-tracking-js', plugins_url( 'pmc-social-share-bar/_build/js/tracking.js', PMC_SOCIAL_SHARE_BAR_ROOT ), array( 'jquery' ), '1.0', true );
		wp_localize_script( 'pmc-social-share-bar-frontend-js', 'pmc_share_bar_lob_ga_tracking', array(
			'permalink'  => get_permalink(),
			'is_mobile'  => ( PMC::is_mobile() ) ? true : false,
			'share_list' => $this->_config->get_social_share_icons(),
		) );
	}

	/**
	 * Prevent KSES from stripping sharing URLs that use custom protocols.
	 *
	 * @param array $protocols Allowed URL protocols.
	 * @return array
	 */
	public function allowed_url_protocols( array $protocols ): array {
		$protocols[] = 'whatsapp';

		return $protocols;
	}

	/**
	 *
	 * Render the Social Share Bar
	 *
	 * @since 2016-02-11
	 * @version 2016-02-11 Archana Mandhare - PMCVIP-815
	 *
	 */
	public function render() {
		global $post;
		++self::$called;

		$permalink = get_permalink( get_the_ID() );
		$title     = get_the_title( get_the_ID() );
		$post_type = '';

		if ( empty( $permalink ) || empty( $title ) ) {
			return;
		}
		if ( ! empty( $post->post_type ) ) {
			$post_type = sanitize_title( $post->post_type );
		}

		$primary_icons = $this->get_icons_from_cache( Admin::PRIMARY, $post_type );

		$secondary_icons = $this->get_icons_from_cache( Admin::SECONDARY, $post_type );

		if ( ! empty( $primary_icons ) && ! empty( $secondary_icons ) ) {
			echo PMC::render_template( PMC_SOCIAL_SHARE_BAR_ROOT . '/_build/svg/pmc-social-icons.svg', array());
			echo PMC::render_template( PMC_SOCIAL_SHARE_BAR_ROOT . '/templates/frontend.php', array(
				'primary_share_icons'   => $primary_icons,
				'secondary_share_icons' => $secondary_icons
			) );
		}
	}

	/**
	 *
	 * Get the icons from cache and if not in cache then fetch from API and save it in cache
	 *
	 * @since 2016-03-11
	 * @version 2016-03-11 Archana Mandhare - PMCVIP-815
	 *
	 * @param $list_name string icon type - primary or secondary
	 *
	 * @return array
	 *
	 */
	public function get_icons_from_cache( $list_name, $post_type = '', int $current_post_id = 0 ) {

		$current_post_id = ( 0 < $current_post_id ) ? $current_post_id : get_the_ID();
		$permalink       = get_permalink( $current_post_id );
		$title           = get_the_title( $current_post_id );

		if ( Admin::PRIMARY === $list_name ) {
			$icons_list = $this->_api->get_share_icons( Admin::PRIMARY, $post_type );
		} else {
			$icons_list = $this->_api->get_share_icons( Admin::SECONDARY, $post_type );
		}

		// Use filter below to hide a certain icon on specific pages
		// such as video landing page in VY should not have comment etc.
		// So unset that using this filter.
		// DO NOT USE THESE FILTERS TO ADD MORE ICONS TO THE LIST.
		// THAT SHOULD BE DONE ONLY USING THE register FUNCTION IN THE ADMIN CLASS.

		$icons_list = apply_filters( "pmc_{$list_name}_icons_display", $icons_list );

		if ( empty( $icons_list ) ) {
			return;
		}

		foreach ( $icons_list as $icon_id ) {
			$icon              = $this->_config->get_social_share_icons_object( $icon_id );

			$icon              = apply_filters( 'pmc_social_share_bar_icon_config', $icon );
			$permalink         = apply_filters( 'pmc_social_share_bar_tracking_url', get_permalink( $current_post_id ), self::$called, $icon_id );
			$icon              = $icon->set_share_url( $icon_id, $permalink, $title );
			$icons[ $icon_id ] = $icon;
		}

		return $icons;
	}

} // end class

//EOF
