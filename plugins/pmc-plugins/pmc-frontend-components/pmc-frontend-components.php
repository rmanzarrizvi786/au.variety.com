<?php
/*
Plugin Name: PMC Frontend Components
Plugin URI: https://www.pmc.com
Description: A collection of components for use on Frontend of a site
Version: 1.1
Author: Amit Gupta, PMC
License: PMC proprietary. All rights reserved.
*/

define( 'PMC_FRONTEND_COMPONENTS_ROOT', untrailingslashit( __DIR__ ) );
define( 'PMC_FRONTEND_COMPONENTS_VERSION', '1.1' );
define( 'PMC_FRONTEND_COMPONENTS_URL', plugins_url( '', __FILE__ ) );


function pmc_frontend_components_loader() {

	/*
	 * Load dependencies
	 */
	require_once PMC_FRONTEND_COMPONENTS_ROOT . '/dependencies.php';

	/*
	 * Enable only those components here which are must-have on all sites
	 * that activate this plugin. Otherwise each component should be activated
	 * individually on a site so that unnecessary stuff is not loaded on sites
	 * which use this plugin.
	 */

}

pmc_frontend_components_loader();



//EOF
