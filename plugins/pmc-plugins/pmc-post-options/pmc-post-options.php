<?php
/*
Plugin Name: PMC Post Options
Plugin URI: http://www.pmc.com
Description: Adds a custom taxonomy to allow different options for a post of any type
Version: 1.0
Author: PMC, Amit Gupta
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_POST_OPTIONS_ROOT', __DIR__ );
define( 'PMC_POST_OPTIONS_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

define( 'PMC_POST_OPTIONS_VERSION', '1.0' );


function pmc_post_options_loader() {

	/*
	 * Load dependencies
	 */
	require_once PMC_POST_OPTIONS_ROOT . '/dependencies.php';

	/*
	 * Initialize PMC Post Options API.
	 */
	\PMC\Post_Options\API::get_instance();

	/*
	 * Initialize PMC Post Options Exclude_Posts.
	 */
	\PMC\Post_Options\Exclude_Posts::get_instance();

	/*
	 * Initialize Connections service to connect to other instances
	 */
	\PMC\Post_Options\Service\Connections::get_instance();

}

pmc_post_options_loader();



//EOF
