<?php
/**
 * Plugin Name:     PMC Store Products
 * Description:     Manages references to produces that live in other stores.
 * Author:          PMC, Daniel Bachhuber, Mike Auteri
 * Domain Path:     /languages
 * Version:         1.0
 *
 * @package         PMC_Store_Products
 */
namespace PMC\Store_Products;

/**
 * Only add code to initialize the plugin here
 */
function init_plugin() {
	Fields::get_instance();
	Shortcode::get_instance();
	Setup::get_instance();
	if ( \PMC::is_wp_cli() ) {
		\WP_CLI::add_command( 'pmc-store-products', CLI::class );
	}
}

if ( file_exists( __DIR__ . '/dependencies.php' ) ) {
	require_once( __DIR__ . '/dependencies.php' );
}
pmc_init_plugin( __NAMESPACE__, __DIR__ );
