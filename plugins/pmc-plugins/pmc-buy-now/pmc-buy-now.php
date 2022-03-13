<?php
namespace PMC\Buy_Now;

// Set constants.
define( 'PMC_BUY_NOW_PLUGIN_URL', trailingslashit( plugins_url( null, __FILE__ ) ) );
define( 'PMC_BUY_NOW_PLUGIN_DIR', __DIR__ );
define( 'PMC_BUY_NOW_VERSION', '1.5' );

/**
 * Only add code to initialize the plugin here
 */
function init_plugin() {
	Plugin::get_instance();
	Config::get_instance();
}

if ( file_exists( __DIR__ . '/dependencies.php' ) ) {
	require_once( __DIR__ . '/dependencies.php' );
}
pmc_init_plugin( __NAMESPACE__, __DIR__ );
