<?php
/*
Plugin Name: PMC Related Articles Plugin
Description: Returns Related articles for a Post based on post ID
Version: 4.0
Author: PMC, Amit Gupta
License: PMC Proprietary.  All rights reserved.
*/

if( ! defined('PMC_RELATED_ARTICLES_CACHE_VERSION') ) {
	$pmc_ra_cache_ver = 4;	//current default version of plugin cache

	//allow for site specific version increase for cache busting
	//cache versions would always be full integers & overrides should always be X.YZ
	//any calls on this filter should be made before loading this plugin
	$pmc_ra_cache_ver_override = apply_filters( 'pmc_related_articles_cache_version_override', $pmc_ra_cache_ver );

	define( "PMC_RELATED_ARTICLES_CACHE_VERSION", max( floatval( $pmc_ra_cache_ver ), floatval( $pmc_ra_cache_ver_override ) ) );

	unset( $pmc_ra_cache_ver_override, $pmc_ra_cache_ver );
}

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

/**
 * load up plugin classes
 */
require_once( __DIR__ . '/class-pmc-related-articles.php' );
require_once( __DIR__ . '/class-pmc-related-articles-widget.php' );


/**
 * If plugin instance is not available then create it
 */
if( ! isset( $GLOBALS['pmc_related_articles'] ) || ! is_a( $GLOBALS['pmc_related_articles'], 'PMC_Related_Articles' ) ) {
	//init PMC_Related_Articles class
	$GLOBALS['pmc_related_articles'] = PMC_Related_Articles::get_instance();
}

/**
 * Setup plugin widget for initialization
 */
add_action( 'widgets_init', 'pmc_register_related_articles_widget' );
function pmc_register_related_articles_widget() {
	register_widget( 'PMC_Related_Articles_Widget' );
}


//EOF
