<?php

/**
 * Sticky rail ads
 *
 * @author Vinod Tella <vtella@pmc.com>
 */

namespace PMC\Sticky_Rail_Ads;

use PMC\Global_Functions\Traits\Singleton;

class Ads {

	use Singleton;

	protected function __construct() {
		self::init();
	}

	/**
	 * Initialising the sticky rail ad setup
	 */
	protected function init() {
		if ( is_admin() ) {
			return;
		}

		add_action( 'wp_loaded', array( $this, 'setup_rail_ads' ) );
	}

	/**
	 * Setting up sticky rail ads
	 */
	public function setup_rail_ads() {
		$pmc_sticky_rail_ads = ( \PMC_Cheezcap::get_instance()->get_option( 'pmc_sticky_rail_ads' ) );
		if ( ! empty ( $pmc_sticky_rail_ads ) && 'yes' === $pmc_sticky_rail_ads ) {
			if ( \PMC::is_desktop() ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			}
		}
	}

	/**
	 * Enqueueing scripts that are required for sticky rail ads
	 */
	public function enqueue_scripts() {
		$script_extension = '.js';
		if ( \PMC::is_production() ) {
			$script_extension = '.min.js';
		}
		$sticky_rail_ads_script = PMC_STICKY_RAIL_ADS_URL . '/assets/js/sticky-rail-ads' . $script_extension;

		pmc_js_libraries_enqueue_script( 'pmc-scrolltofixed' );
		wp_enqueue_script( 'pmc_sticky_rail_ads-js', $sticky_rail_ads_script, array( 'jquery', 'underscore', 'pmc-scrolltofixed' ), '1.0.0', true );

		$config = [
			'rail_selector'      => \PMC_Cheezcap::get_instance()->get_option( 'pmc_sticky_rail_ads_parent' ),
			'first_ad_selector'  => \PMC_Cheezcap::get_instance()->get_option( 'pmc_sticky_rail_ads_first' ),
			'second_ad_selector' => \PMC_Cheezcap::get_instance()->get_option( 'pmc_sticky_rail_ads_second' ),
			'nav_bar_selector'   => \PMC_Cheezcap::get_instance()->get_option( 'pmc_sticky_rail_ads_nav_bar' ),
			'admin_bar_selector' => \PMC_Cheezcap::get_instance()->get_option( 'pmc_sticky_rail_ads_admin_bar' ),
			'first_ad_limit'     => (int) \PMC_Cheezcap::get_instance()->get_option( 'pmc_sticky_rail_ads_first_ad_scroll' ),
			'ad_container_width' => (int) \PMC_Cheezcap::get_instance()->get_option( 'pmc_sticky_rail_ads_ad_container_width' ),
			'is_dynamic_content' => is_singular( 'pmc-gallery' ),
		];

		$config = apply_filters( 'pmc_sticky_rail_ads', $config );

		wp_localize_script( 'pmc_sticky_rail_ads-js', 'pmc_sticky_rail_ads', $config );
	}

}
