<?php
/*
Plugin Name: PMC Redirect Overlay
Plugin URI: https://pmc.com/
Description: Adds an overlay banner for redirects.
Version: 1.0
Author: PMC, Amit Gupta
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_REGION_REDIRECT_OVERLAY_ROOT', __DIR__ );
define( 'PMC_REGION_REDIRECT_OVERLAY_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

define( 'PMC_REGION_REDIRECT_OVERLAY_VERSION', '1.0' );

function pmc_region_redirect_overlay_loader() {

	/*
	 * Load dependencies
	 */
	require_once __DIR__ . '/dependencies.php';

	\PMC\Region_Redirect_Overlay\Admin::get_instance();
	\PMC\Region_Redirect_Overlay\Services\Frontend::get_instance();

}

pmc_region_redirect_overlay_loader();

//EOF
