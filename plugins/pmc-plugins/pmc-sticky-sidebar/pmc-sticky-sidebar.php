<?php
/*
Plugin Name: PMC Sticky Sidebar
Plugin URI: http://www.pmc.com
Description:
Version: 1.0
Author: Jignesh Nakrani, PMC
License: PMC Proprietary. All rights reserved.
Text Domain: pmc-sticky-sidebar
*/

define( 'PMC_STICKY_SIDEBAR_ROOT', __DIR__ );
define( 'PMC_STICKY_SIDEBAR_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

function pmc_sticky_sidebar_loader() {

	/**
	 * Initialize PMC Sticky sidebar service
	 */
	\PMC\Sticky_Sidebar\Sticky_Sidebar::get_instance();

}

pmc_sticky_sidebar_loader();

//EOF
