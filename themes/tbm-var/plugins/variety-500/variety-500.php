<?php
/**
 * Plugin Name: Variety 500
 * Description: Adds Variety 500 functionality to the Variety.com website and
 * allows executive profiles to be marked as a 500 profile.
 * Also includes a search feature and templating.
 *
 * Version: 1.0
 * Author: PMC, XWP
 * Text Domain: pmc-variety
 * License: PMC proprietary. All rights reserved.
 *
 * @package pmc-variety-2017
 */

define( 'VARIETY_500_ROOT', __DIR__ );
define( 'VARIETY_500_PLUGIN_URL', get_stylesheet_directory_uri() . '/plugins/variety-500' );

/**
 * Plugin Loader.
 */
function variety_500_loader() {

	// Include dependencies.
	require_once( untrailingslashit( VARIETY_500_ROOT ) . '/dependencies.php' );

	// Load initial class.
	\Variety\Plugins\Variety_500\Bootstrap::get_instance();

}

variety_500_loader();

//EOF
