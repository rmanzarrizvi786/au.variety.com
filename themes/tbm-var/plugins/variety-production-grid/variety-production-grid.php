<?php
/*
Plugin Name: Variety Production Grid
Plugin URI: http://www.variety.com
Description: A Production Grid
Version: 1.0
Author: Adam Holisky
Author URI: http://www.pmc.com
Author Email: adam.holisky@gmail.com
License: PMC proprietary. All rights reserved.
*/

if ( ! defined( 'VARIETY_PRODUCTION_GRID_URL' ) ) {
	define( 'VARIETY_PRODUCTION_GRID_URL', get_stylesheet_directory_uri() . '/plugins/variety-production-grid' );
}

if ( ! defined( 'VARIETY_THEME_URL' ) ) {
	define( 'VARIETY_THEME_URL', get_stylesheet_directory_uri() );
}

require_once( __DIR__ . '/classes/class-variety-production-grid.php' );

Variety_Production_Grid::get_instance();
