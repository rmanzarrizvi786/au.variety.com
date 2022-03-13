<?php
/*
Plugin Name: PMC Piano
Plugin URI: http://www.pmc.com
Description: A Plugin that adds integration with Piano
Version: 2.0.4
Author: PMC
Text Domain: pmc-piano
License: PMC Proprietary. All rights reserved.
*/
define( 'PMC_PIANO_VERSION', '2.0.4' );
define( 'PMC_PIANO_URI', plugin_dir_url( __FILE__ ) );
define( 'PMC_PIANO_ROOT', __DIR__ );

require_once __DIR__ . '/dependencies.php';

PMC\Piano\Plugin::get_instance();
PMC\Piano\Pages::get_instance();
PMC\Piano\Paid_Content::get_instance();
PMC\Piano\Bot::get_instance();
PMC\Piano\Cxense::get_instance();
PMC\Piano\Subscribe_Rewrite::get_instance();
PMC\Piano\Amp::get_instance();
PMC\Piano\Reporting::get_instance();

//EOF
