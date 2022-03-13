<?php
/*
Plugin Name: PMC Content Exchange
Plugin URI: http://www.pmc.com
Description: Content exchange plugin for PMC sites
Version: 0.1
Author: Amit Gupta, PMC
License: PMC Proprietary. All rights reserved.
Text Domain: pmc-content-exchange
Domain Path: /languages
*/

define( 'PMC_CONTENT_EXCHANGE_ROOT', __DIR__ );
define( 'PMC_CONTENT_EXCHANGE_URL', plugin_dir_url( __FILE__ ) );
define( 'PMC_CONTENT_EXCHANGE_VERSION', '0.1' );


function pmc_content_exchange_loader() {

	/*
	 * Register widget
	 */
	add_action( 'widgets_init', function() {
		register_widget( '\PMC\Content_Exchange\Widget' );
	} );

}

pmc_content_exchange_loader();

add_action( 'plugins_loaded', function () {

	load_plugin_textdomain( 'pmc-content-exchange', false, basename( dirname( __FILE__ ) ) . '/languages' );
} );

//EOF
