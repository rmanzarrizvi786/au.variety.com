<?php
/**
 * Plugin Name: Variety VIP
 * Description: Adds Variety Intelligence Platform functionality to the Variety.com website.
 *
 * Version: 1.0
 * Author: PMC, XWP
 * Text Domain: pmc-variety
 * License: PMC proprietary. All rights reserved.
 *
 * @package pmc-variety-2020
 */

define( 'VARIETY_VIP_ROOT', __DIR__ );
define( 'VARIETY_VIP_PLUGIN_URL', get_stylesheet_directory_uri() . '/plugins/variety-vip' );

/**
 * Plugin Loader.
 *
 * @codeCoverageIgnore
 */
function variety_vip_loader() {

	// Include dependencies.
	require_once( untrailingslashit( VARIETY_VIP_ROOT ) . '/dependencies.php' );

	// Load initial class.
	\Variety\Plugins\Variety_VIP\VIP::get_instance();

}

variety_vip_loader();

//EOF

