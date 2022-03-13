<?php
/*
Plugin Name: PMC Sticky Ads
Plugin URI: http://www.pmc.com
Description: Adds sticky ads to pages
Version: 1.0
Author: Amit Gupta, PMC
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_STICKY_ADS_ROOT', __DIR__ );
define( 'PMC_STICKY_ADS_VERSION', '1.0' );


function pmc_sticky_ads_loader() {

	require_once PMC_STICKY_ADS_ROOT . '/dependencies.php';

	/*
	 * Initialize services
	 */
	\PMC\Sticky_Ads\Service\Gate_Keeper::get_instance();

}

pmc_sticky_ads_loader();



//EOF
