<?php

/*
	Plugin Name: PMC Inject Ad Placeholders
	Plugin URI: http://pmc.com/
	Description: Plugin to inject ad placeholders (empty divs) into content.
				 This allows 3rd-party ad vendors to render their ads into
				 placeholders we define.
	Version: 1.0
	Author: PMC, James Mehorter
	License: PMC Proprietary. All rights reserved.
	Text Domain: pmc-ad-placeholders
	Domain Path: /languages
*/

namespace PMC\Ad_Placeholders;

define( 'PMC_AD_PLACEHOLDERS_DIR', trailingslashit( __DIR__ ) );
define( 'PMC_AD_PLACEHOLDERS_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

// Include plugin dependencies
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

// Initiate the plugin's admin options
Admin::get_instance();

// Initiate the in-post ad placeholder injection
Injection::get_instance();

/**
 * Load text domain.
 */
function pmc_ad_placeholders_load_text_domain() {
	load_plugin_textdomain( 'pmc-ad-placeholders', false, basename( PMC_AD_PLACEHOLDERS_DIR ) . '/languages' );
}

add_action( 'plugins_loaded', '\PMC\Ad_Placeholders\pmc_ad_placeholders_load_text_domain' );
