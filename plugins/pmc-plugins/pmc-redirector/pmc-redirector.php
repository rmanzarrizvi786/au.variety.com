<?php
/*
Plugin Name: PMC Redirector
Plugin URI: http://www.pmc.com
Description: Adds URL redirection
Version: 1.0
Author: Amit Gupta, PMC
License: PMC Proprietary. All rights reserved.
Text Domain: pmc-redirector
Domain Path: /languages
*/

define( 'PMC_REDIRECTOR_ROOT', __DIR__ );
define( 'PMC_REDIRECTOR_VERSION', '1.0' );


function pmc_redirector_loader() {

	require_once PMC_REDIRECTOR_ROOT . '/dependencies.php';

	/*
	 * Initialize services
	 */
	\PMC\Redirector\Services\Redirector::get_instance();

}

pmc_redirector_loader();

/**
 * Load text domain.
 */
function pmc_redirector_load_text_domain() {
	load_plugin_textdomain( 'pmc-redirector', false, basename( PMC_REDIRECTOR_ROOT ) . '/languages' );
}

add_action( 'plugins_loaded', 'pmc_redirector_load_text_domain' );

//EOF
