<?php
/*
Plugin Name: PMC Event Tracking
Plugin URI: http://www.pmc.com
Description: Adds event tracking to various parts
Version: 1.0
Author: Amit Gupta, PMC
License: PMC Proprietary. All rights reserved.
Text Domain: pmc-event-tracking
Domain Path: /languages
*/

define( 'PMC_EVENT_TRACKING_ROOT', __DIR__ );
define( 'PMC_EVENT_TRACKING_VERSION', '1.0' );


function pmc_event_tracking_loader() {

	require_once PMC_EVENT_TRACKING_ROOT . '/dependencies.php';

	/*
	 * Initialize services
	 */
	\PMC\Event_Tracking\Service\Single_Post::get_instance();

}

pmc_event_tracking_loader();

/**
 * Load text domain.
 */
function pmc_event_tracking_load_textdomain() {
	load_plugin_textdomain( 'pmc-event-tracking', false, basename( PMC_EVENT_TRACKING_ROOT ) . '/languages' );
}

add_action( 'plugins_loaded', 'pmc_event_tracking_load_textdomain' );

//EOF
