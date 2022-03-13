<?php
/**
 * Plugin Name: PMC CCPA
 * Description: Plugin to setup IAB framework to interact with CCPA user signal
 * Version: 1.0.0
 * @package pmc-plugins
 */

define( 'PMC_CCPA_VERSION', '1.0' );
define( 'PMC_CCPA_ROOT', __DIR__ );
define( 'PMC_CCPA_URL', plugins_url( '', __FILE__ ) );

/**
 * Initialize PMC CCPA plugin
 */
function pmc_ccpa_loader() {

	\PMC\CCPA\Plugin::get_instance();

}

pmc_ccpa_loader();

//EOF
