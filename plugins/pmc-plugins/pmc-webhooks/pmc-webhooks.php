<?php
/*
Plugin Name: PMC Webhooks
Plugin URI: https://www.pmc.com
Description: A collection of Webhooks for integration with 3rd party services
Version: 0.1
Author: Amit Gupta, PMC
License: PMC proprietary. All rights reserved.
*/

define( 'PMC_WEBHOOKS_ROOT', untrailingslashit( __DIR__ ) );
define( 'PMC_WEBHOOKS_VERSION', '0.1' );
define( 'PMC_WEBHOOKS_URL', plugins_url( '', __FILE__ ) );


function pmc_webhooks_loader() {

	/*
	 * Load dependencies
	 */
	require_once PMC_WEBHOOKS_ROOT . '/dependencies.php';

	// Setup Services
	\PMC\Webhooks\Services\Slack::get_instance();

}

pmc_webhooks_loader();



//EOF
