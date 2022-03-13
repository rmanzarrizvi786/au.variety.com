<?php
/*
Plugin Name: PMC Rollbar
Plugin URI: https://pmc.com
Description: Helper functionalities for rollbar
Version: 1.0
License: PMC Proprietary. All rights reserved.
Author: Sub Devs
*/

define( 'PMC_ROLLBAR_ROOT', __DIR__ );
define( 'PMC_ROLLBAR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PMC_ROLLBAR_VERSION', '2.19.4' );  // This version string must match rollbar release version references

require_once __DIR__ . '/dependencies.php';

PMC\Rollbar\Plugin::get_instance();
PMC\Rollbar\Loader::get_instance();
