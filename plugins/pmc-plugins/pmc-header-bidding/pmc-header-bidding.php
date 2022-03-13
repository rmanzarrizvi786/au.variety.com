<?php
/*
	Plugin Name: PMC Header Bidding
	Plugin URI: http://pmc.com/, http://prebid.org/dev-docs/getting-started.html
	Description: Plugin to provide option to Ad publishers to implement header bidding at a single place using prebid.js which is an open source library
	Version: 1.0
	Author: Archana Mandhare, PMC
	License: PMC Proprietary.  All rights reserved.
	Text Domain: pmc-header-bidding
	Domain Path: /languages
*/

define( 'PMC_HEADER_BIDDING_DIR', trailingslashit( __DIR__ ) );
define( 'PMC_HEADER_BIDDING_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

/**
* pmc_header_bidding_loader
*
*/
function pmc_header_bidding_loader() {

	// Include vendors
	include_once( PMC_HEADER_BIDDING_DIR . 'vendors/base.php' );
	include_once( PMC_HEADER_BIDDING_DIR . 'vendors/appnexus.php' );
	include_once( PMC_HEADER_BIDDING_DIR . 'vendors/indexExchange.php' );
	include_once( PMC_HEADER_BIDDING_DIR . 'vendors/pubmatic.php' );
	include_once( PMC_HEADER_BIDDING_DIR . 'vendors/sovrn.php' );
	include_once( PMC_HEADER_BIDDING_DIR . 'vendors/triplelift.php' );
	include_once( PMC_HEADER_BIDDING_DIR . 'vendors/audienceNetwork.php' );
	include_once( PMC_HEADER_BIDDING_DIR . 'vendors/sonobi.php' );
	include_once( PMC_HEADER_BIDDING_DIR . 'vendors/rubicon.php' );
	include_once( PMC_HEADER_BIDDING_DIR . 'vendors/rubiconlite.php' );

	// Initiate header bidding
	PMC\Header_Bidding\Library::get_instance();
	PMC\Header_Bidding\Vendors\RubiconLite::get_instance();
}

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
pmc_header_bidding_loader();

/**
 * Load text domain.
 */
function pmc_header_bidding_text_domain() {
	load_plugin_textdomain( 'pmc-header-bidding', false, basename( PMC_HEADER_BIDDING_DIR ) . '/languages' );
}

add_action( 'plugins_loaded', 'pmc_header_bidding_text_domain' );
