<?php
/*
	Plugin Name: PMC Amazon Apstag Header bidder
	Plugin URI: http://pmc.com/
	Description: Plugin to integrate Amazon Apstag header bidding
	Version: 1.0
	Author: PMC, Vinod Tella
	License: PMC Proprietary. All rights reserved.
	Text Domain: pmc-amazon-apstag
	Domain Path: /languages
*/

namespace PMC\Amazon_Apstag;

define( 'PMC_AMAZON_APSTAG_DIR', trailingslashit( __DIR__ ) );
define( 'PMC_AMAZON_APSTAG_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

// Include plugin dependencies
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

// Initiate the plugin's admin options

Admin::get_instance();

// Initiate the Apstag header bidding
Apstag::get_instance();

/**
 * Load text domain.
 */
function pmc_amazon_apstag_load_text_domain() {
	load_plugin_textdomain( 'pmc-amazon-apstag', false, basename( PMC_AMAZON_APSTAG_DIR ) . '/languages' );
}
add_action( 'plugins_loaded', '\PMC\Amazon_Apstag\pmc_amazon_apstag_load_text_domain' );
