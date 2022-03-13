<?php
/**
 * Plugin Name: PMC Zoninator Extended.
 * Version: 1.0.0
 * Author: PMC
 * License: PMC Proprietary.  All rights reserved.
 * Slug : pmc-zoninator-extended
 *
 * @package pmc-plugins
 */

if ( ! defined( 'PMC_ZONINATOR_EXTENDED_DIR' ) ) {
	define( 'PMC_ZONINATOR_EXTENDED_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
}
if ( ! defined( 'PMC_ZONINATOR_EXTENDED_URI' ) ) {
	define( 'PMC_ZONINATOR_EXTENDED_URI', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
}

if ( ! function_exists( 'pmc_zoninator_extended_loader' ) ) {

	/**
	 * Function to initialize plugin.
	 *
	 * @return void
	 */
	function pmc_zoninator_extended_loader() {

		// Load plugin dependencies.
		require_once( __DIR__ . '/dependencies.php' );

		// Initialize Init class. and return instance of class.
		\PMC\Zoninator_Extended\Init::get_instance();

		// Initialize Edit_Posts class.
		\PMC\Zoninator_Extended\Edit_Posts::get_instance();
	}

	pmc_zoninator_extended_loader();
}
