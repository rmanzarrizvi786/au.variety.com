<?php
/*
Plugin Name: PMC Sticky Rail Ads
Plugin URI: http://www.pmc.com
Description:
Version: 1.0
Author: Vinod Tella, PMC
License: PMC Proprietary. All rights reserved.
Text Domain: pmc-sticky-rail-ads
Domain Path: /languages
*/

define( 'PMC_STICKY_RAIL_ADS_ROOT', __DIR__ );
define( 'PMC_STICKY_RAIL_ADS_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

function pmc_sticky_rail_ad_loader() {

	/*
	 * Initialize PMC Sticky Posts service for homepage
	 */
	\PMC\Sticky_Rail_Ads\Admin::get_instance();
	\PMC\Sticky_Rail_Ads\Ads::get_instance();

}

pmc_sticky_rail_ad_loader();

/**
 * Load text domain.
 */
function pmc_sticky_rail_ads_load_text_domain() {
	load_plugin_textdomain( 'pmc-sticky-rail-ads', false, basename( PMC_STICKY_RAIL_ADS_ROOT ) . '/languages' );
}
add_action( 'plugins_loaded', 'pmc_sticky_rail_ads_load_text_domain' );

//EOF