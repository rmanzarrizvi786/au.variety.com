<?php
/*
Plugin Name: Variety Vscore Top
Plugin URI: http://variety.com
Description: A Production Grid
Version: 1.0
Author: Adam Holisky
Author URI: http://pmc.com
Author Email: adam.holisky@gmail.com
License: PMC proprietary. All rights reserved.
*/

if ( ! defined( 'VARIETY_VSCORE_TOP_URL' ) ) {
	define( 'VARIETY_VSCORE_TOP_URL', get_stylesheet_directory_uri() . '/plugins/variety-vscore-top' );
}

if ( ! defined( 'VARIETY_THEME_URL' ) ) {
	define( 'VARIETY_THEME_URL', get_stylesheet_directory_uri() );
}
require_once( __DIR__ . '/classes/class-variety-vscore-top.php' );

Variety_Vscore_Top::get_instance();
