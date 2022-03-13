<?php

/*
Plugin Name: PMC cXense
Plugin URI: http://www.pmc.com/
Description: A Plugin that adds integration with cXense
Version: 1.0
Author: PMC
Text Domain: pmc-cxense
License: PMC Proprietary.  All rights reserved.
 */

/**
 * This plugins adds:
 * 1
 *
 *
 * Cxense needs to be passed the following:
 * site id
 * custom parameters OR add meta tags if cxensebot detected
 * widget ids and corresponding div ids
 *
 * This plugin needs meta tag data, custom data, module info
 */
define( 'PMC_CXENSE_DIR', __DIR__ );
define( 'PMC_CXENSE_URI', plugin_dir_url( __FILE__ ) );
define( 'PMC_CXENSE_VERSION', '1.0' );

require_once PMC_CXENSE_DIR . '/dependencies.php';

PMC\Cxense\Plugin::get_instance();
PMC\Cxense\Bot::get_instance();

//EOF
