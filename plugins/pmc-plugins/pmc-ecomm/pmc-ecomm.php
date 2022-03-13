<?php
/**
 * Plugin Name: PMC E-Commerce
 * Description: Consolidate all e-commerce related features: buy now, amazon product, etc.
 * Author:      PMC, Hau Vong
 * Version:     1.0
 *
 * @package     PMC\EComm
 */
namespace PMC\EComm;

define( 'PMC_ECOMM_VERSION', '2021.1' );
define( 'PMC_ECOMM_DIR', untrailingslashit( __DIR__ ) );
define( 'PMC_ECOMM_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

/**
 * Only add code to initialize the plugin here
 * Initialize the plugin by instantiate all required singleton class objects
 */
function init_plugin() {
	Tracking::get_instance();
	Embed::get_instance();
	Anchor::get_instance();
	Disclaimer::get_instance();
}

if ( file_exists( __DIR__ . '/dependencies.php' ) ) {
	require_once( __DIR__ . '/dependencies.php' );
}
pmc_init_plugin( __NAMESPACE__, __DIR__ );
//EOF
