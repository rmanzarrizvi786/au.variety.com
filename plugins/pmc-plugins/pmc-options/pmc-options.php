<?php
/*
Plugin Name: PMC Options
Plugin URI: http://www.pmc.com
Description: A plugin to mimic wp_options functionality using custom post type. Using wp_options for huge data set is performance nightmare.
Version: 0.1
Author: Amit Sannad
Author URI: http://www.pmc.com
License: PMC Proprietary.  All rights reserved.
*/

require_once( __DIR__ . '/class-pmc-options.php' );

$pmc_options = PMC_Options::get_instance();

/**
 * PMC version of get option
 * @param $name
 * @param string $group_name
 * @return mixed
 */
function pmc_get_option( $name, $group_name = 'general' ){

	$pmc_options = PMC_Options::get_instance( $group_name );
	return $pmc_options->get_option( $name );

}

function pmc_get_options( $group_name = 'general' ){
	$pmc_options = PMC_Options::get_instance( $group_name );
	return $pmc_options->get_options();

}

/**
 * PMC version of add option
 * @param $name
 * @param $value
 * @param string $group_name
 * @return mixed
 */
function pmc_add_option( $name, $value, $group_name = 'general' ){

	$pmc_options = PMC_Options::get_instance( $group_name );
	return $pmc_options->add_option( $name, $value );

}

/**
 * PMC version of update option
 * @param $name
 * @param $value
 * @param string $group_name
 * @return mixed
 */
function pmc_update_option( $name, $value, $group_name = 'general' ){

	$pmc_options = PMC_Options::get_instance( $group_name );
	return $pmc_options->update_option( $name, $value );

}

/**
 * PMC version of delete option
 * @param $name
 * @param string $group_name
 * @return mixed
 */
function pmc_delete_option( $name, $group_name = 'general' ){

	$pmc_options = PMC_Options::get_instance( $group_name );
	return $pmc_options->delete_option( $name );

}

/**
 * A simple wrapper for filtering pmc_update_option
 * @param $new_value, $old_value, $opt
 * @return mixed
 */
function pmc_update_filtered_option( $new_value, $old_value, $opt ) {
	if ( function_exists( 'pmc_update_option' ) ) {
		return pmc_update_option( $opt, $new_value );
	} else {
		return $new_value;
	}
}

/**
 * A simple wrapper for filtering pmc_get_option
 * @param $val, $opt
 * @return mixed
 */
function pmc_get_filtered_option( $val, $opt ) {
	if ( function_exists( 'pmc_get_option' ) ) {
		return pmc_get_option( $opt );
	} else {
		return $val;
	}
}

function pmc_override_option( $opt_name ) {
	add_filter( 'pre_option_' . $opt_name, 'pmc_get_filtered_option', 10, 2 );
	add_filter( 'pre_option_cap_' . $opt_name, 'pmc_get_filtered_option', 10, 2 );
	add_filter( 'pre_update_option_' . $opt_name, 'pmc_update_filtered_option', 10, 3 );
	add_filter( 'pre_update_option_cap_' . $opt_name, 'pmc_update_filtered_option', 9, 3 );
}

//EOF
