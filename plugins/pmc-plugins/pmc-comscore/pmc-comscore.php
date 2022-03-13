<?php
/*
Plugin Name: PMC comScore
Description: Implement pageview_candidate comScore tracking
Version: 1.0
License: PMC Proprietary.  All rights reserved.

usage in javascript when ajax call is triggered that need tracking:
pmc_comscore.pageview()

*/

if ( ! defined( 'PMC_COMSCORE_VERSION' ) ) {
	define( 'PMC_COMSCORE_VERSION', '1.1' );
}

add_action( 'wp_enqueue_scripts', function() {
	// output the js variable pmc_comscore_pageview_candidate_url pointing to plugin path for .xml file
	$pageview_url = plugins_url( 'pmc-comscore/xml/comscore-pageview-candidate.xml', __DIR__ );
	// we don't want the admin url
	$pageview_url = str_replace( parse_url( $pageview_url, PHP_URL_HOST ), parse_url( home_url(), PHP_URL_HOST ), $pageview_url );
	//we don't want URL scheme in URL either, it should be assumed same as current page URL's scheme
	$pageview_url = str_replace( sprintf( '%s://', parse_url( $pageview_url, PHP_URL_SCHEME ) ), '//', $pageview_url );

	$pageview_url = apply_filters( 'pmc-comscore-pageview-candidate-url', $pageview_url );

	wp_register_script( 'pmc-comscore', plugins_url( 'pmc-comscore/js/pmc-comscore.js', __DIR__ ), array( 'jquery' ), PMC_COMSCORE_VERSION );
	wp_localize_script( 'pmc-comscore', 'pmc_comscore_options', array( 'pageview_candidate_url' => $pageview_url ) );
	wp_enqueue_script( 'pmc-comscore' );
});

