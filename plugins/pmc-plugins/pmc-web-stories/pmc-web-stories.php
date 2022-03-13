<?php
/**
 * Plugin Name: PMC Web stories
 * Description: PMC web stories.
 * Version: 1.0
 * Author: PMC
 * License: PMC Proprietary.  All rights reserved.
 * Text Domain: pmc-web-stories
 *
 * @package pmc-web-stories
 */

use PMC\Web_Stories\User_Controller;

//define path to plugin root - all paths inside plugin are referenced from here
define( 'PMC_WEBSTORIES_DIR', __DIR__ );

if ( ! defined( 'WEBSTORIES_DEV_MODE' ) ) {
	define( 'WEBSTORIES_DEV_MODE', true );
}

pmc_load_plugin( 'web-stories', 'pmc-plugins' );
pmc_load_plugin( 'pmc-google-universal-analytics', 'pmc-plugins' );
pmc_load_plugin( 'co-authors-plus', false, '3.4' );

\PMC\Web_Stories\Web_Stories::get_instance();
\PMC\Web_Stories\Analytics::get_instance();
\PMC\Web_Stories\User_Controller::get_instance();
