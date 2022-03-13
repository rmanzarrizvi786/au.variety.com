<?php
/**
Plugin Name: PMC Exacttarget
Description: Interfaces with ET and provides an admin UI
Version: 1.0
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_EXACTTARGET_VERSION', '1.2.0' );
define( 'PMC_EXACTTARGET_PATH', __DIR__ );

if ( ! defined( 'PMC_EXACTTARGET_PLUGIN_URL' ) ) {
	define( 'PMC_EXACTTARGET_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
}

require_once( __DIR__ . "/dependencies.php" );
require_once( __DIR__ . '/sailthru.php' );

PMC\Exacttarget\Cron::get_instance();

//EOF
