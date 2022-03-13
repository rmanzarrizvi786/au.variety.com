<?php
// phpcs:disable WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable

/*
Plugin Name: PMC Ads
Plugin URI: https://pmc.com/
Description: Ad Manager plugin which allows serving different types of ads from one or more ad providers. For usage see docs at https://confluence.pmcdev.io/label/pmcdocs/admanager
Version: 3.0
Author: PMC, mjohnson, Amit Sannad, Amit Gupta
License: PMC Proprietary.  All rights reserved.
*/

//define path to plugin root - all paths inside plugin are referenced from here
define( 'PMC_ADM_DIR', __DIR__ );
define( 'DEFAULT_AD_PROVIDER', 'google-publisher' );
define( 'PMC_ADM_VERSION', '2021.1' );

/**
 * Load dependencies
 */
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
pmc_load_plugin( 'pmc-geo-uniques', 'pmc-plugins' );
pmc_geo_add_location( 'us' ); // USA
pmc_geo_add_location( 'gb' ); // United Kingdom
pmc_geo_add_location( 'ca' ); // Canada
pmc_geo_add_location( 'au' ); // Australia


/**
 * Require classes.
 */
require_once( PMC_ADM_DIR . '/class-pmc-ad-provider.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ads-role.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ads.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ads-interruptus.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ads-widget.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ads-location-widget.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ads-exporter.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ads-importer.php' );
require_once( PMC_ADM_DIR . '/providers/adsense.php' );
require_once( PMC_ADM_DIR . '/providers/double-click.php' );
require_once( PMC_ADM_DIR . '/providers/double-click-mobile.php' );
require_once( PMC_ADM_DIR . '/providers/google-publisher.php' );
require_once( PMC_ADM_DIR . '/providers/site-served.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ad-dynamic-zone.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ad-conditions.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ad-dfp-prestitial.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ads-dfp-skin.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ad-timegap-trigger.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ads-txt.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ads-floating-preroll-ad.php' );
require_once( PMC_ADM_DIR . '/class-pmc-ads-contextual-player-ad.php' );
require_once( PMC_ADM_DIR . '/providers/boomerang.php' );

// add WP-CLI command support
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	pmc_load_plugin( 'pmc-wp-cli', 'pmc-plugins' );
}


/**
 * Initialize classes.
 */
PMC_Ads::get_instance();
PMC_Ads_Interruptus::get_instance();
PMC_Ads_Dfp_Prestitial::get_instance();
PMC_Ads_Dfp_Skin::get_instance();
PMC_Ads_Time_Gap_Trigger::get_instance();
PMC_Ads_Txt::get_instance();
PMC_Ads_Role::get_instance();
PMC_Ads_Floating_Preroll_Ad::get_instance();
PMC_Ads_Contextual_Player_Ad::get_instance();

/**
 * Add a provider into the system.
 *
 * @param PMC_Ad_Provider $provider
 *
 * @return PMC_Ads
 */
function pmc_adm_add_provider( PMC_Ad_Provider $provider ) {
	return PMC_Ads::get_instance()->add_provider( $provider );
}

/**
 * Add ad locations into the system.
 *
 * @see PMC_Ads::add_locations
 * @param array $locations
 *
 * @return PMC_Ads
 */
function pmc_adm_add_locations( array $locations ) {
	return PMC_Ads::get_instance()->add_locations( $locations );
}

/**
 * Render all ads within an ad_type.
 *
 * @param string $ad_location - The location for which we need to fetch and render the ad
 * @param string $ad_title - Title of the Ad for display on frontend
 * @param bool $echo where to return the ad html or echo
 * @param string $provider default 'google-publisher'
 *
 * @return string
 */
function pmc_adm_render_ads( $ad_location, $ad_title = '', $echo = true, $provider = '' ) {
	return PMC_Ads::get_instance()->render_ads( $ad_location, $ad_title, $echo, $provider );
}

/**
 * ex: [pmc_ads ads_location="location" ads_title="title"]
 *
 * @param array $atts
 *
 * @return string
 */
function pmc_adm_render_shortcode( $atts ) {
	if ( ! empty( $atts['ads_location'] ) && ! empty( $atts['ads_title'] ) ) {
		return pmc_adm_render_ads( $atts['ads_location'], sanitize_text_field( $atts['ads_title'] ), false );
	}
}

add_shortcode( 'pmc_ads', 'pmc_adm_render_shortcode' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( __DIR__ . '/class-pmc-ads-wp-cli.php' );
}

//EOF
