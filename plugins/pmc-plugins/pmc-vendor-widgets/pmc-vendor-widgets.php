<?php
/*
Plugin Name: PMC Vendor Widgets
Plugin URI: https://www.pmc.com
Description: A collection of 3rd party vendor widgets used on one or more of our sites
Version: 0.1
Author: Amit Gupta, PMC
License: PMC proprietary. All rights reserved.
*/

define( 'PMC_VENDOR_WIDGETS_ROOT', untrailingslashit( __DIR__ ) );
define( 'PMC_VENDOR_WIDGETS_VERSION', '0.1' );
define( 'PMC_VENDOR_WIDGETS_URL', plugins_url( '', __FILE__ ) );


function pmc_vendor_widgets_loader() {

	/*
	 * Load dependencies
	 */
	require_once PMC_VENDOR_WIDGETS_ROOT . '/dependencies.php';

	// Setup WP Widgets
	\PMC\Vendor_Widgets\Widgets::get_instance();

}

pmc_vendor_widgets_loader();



//EOF
