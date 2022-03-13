<?php
/*
Plugin Name: PMC Ceros Embeds
Plugin URI: https://pmc.com/
Description: Adds the shortcode for Ceros embeds
Version: 0.1
Author: PMC, Amit Gupta
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_CEROS_EMBEDS_ROOT', __DIR__ );
define( 'PMC_CEROS_EMBEDS_VERSION', '0.1' );

function pmc_ceros_embeds_loader() : void {

	require_once PMC_CEROS_EMBEDS_ROOT . '/dependencies.php';    // phpcs:ignore

	\PMC\Ceros_Embeds\Admin::get_instance();
	\PMC\Ceros_Embeds\Shortcode::get_instance();

}

pmc_ceros_embeds_loader();

//EOF
