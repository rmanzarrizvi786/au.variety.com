<?php
/*
Plugin Name: PMC Sitemaps
Description: Generates sitemaps.
Version: 2.0.0.2
Author: PMC, Amit Gupta, Gabriel Koen, Sachin Rajput
License: PMC Proprietary.  All rights reserved.
*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

/**
 * Redirect site's sitemap old url sitemap.xml to new sitemap_index.xml url
 * @since 2014-07-29 Sachin Rajput
 */
if ( function_exists('vip_redirects') ) {
	$vip_redirects_array = array(
		'/sitemap.xml' => '/sitemap_index.xml'
	);

	vip_redirects( $vip_redirects_array );
}

if ( ! defined('PMC_SITEMAP_REBUILD_ON_DEMAND') ) {
	define( 'PMC_SITEMAP_REBUILD_ON_DEMAND', 1 );
}

if ( ! defined( 'PMC_SITEMAPS_BASE_FILE' ) ) {
	define( 'PMC_SITEMAPS_BASE_FILE', __FILE__ );
}

/**
 * Disable Core's native sitemaps in favor of ours.
 */
add_filter( 'wp_sitemaps_enabled', '__return_false' );

/**
 * Add sitemap index and news sitemap to robots.txt
 */
function pmc_sitemaps_robots_txt( $output, $public ) {
	if ( $public ) {
		$output .= 'Sitemap: '.home_url('/news-sitemap.xml') . "\n";
		$output .= 'Sitemap: '.home_url('/sitemap_index.xml') . "\n";
	}

	return $output;
}
add_filter( 'robots_txt', 'pmc_sitemaps_robots_txt', 10, 2 );
remove_action( 'do_robotstxt', 'sitemap_discovery', 5 );

// disable plugin if wp is importing
if ( ! defined( 'WP_IMPORTING' ) || WP_IMPORTING !== true ) {

	require_once __DIR__ . '/classes/class-pmc-sitemaps.php';

	$GLOBALS['pmc_sitemaps'] = PMC_Sitemaps::get_instance();

	\PMC\Sitemaps\News_Sitemap::get_instance();
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/wp-cli.php';
}

//EOF
