<?php
/*
Plugin Name: PMC Social Headline
Description: Add Social Headline Metabox and render in custom meta tag on Article page
Version: 1.0
Author: PMC, Archana Mandhare
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_SOCIAL_HEADLINE_ROOT', __DIR__ );

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
pmc_load_plugin( 'custom-metadata' );

/**
 * Initialize Social Headline Metabox Class
 *
 * @since 2015-11-23
 * @version 2015-11-23 Archana Mandhare PMCVIP-541
 *
 */
function pmc_social_headline_loader() {
	\PMC\Social_Headline\Metabox::get_instance();
}

pmc_social_headline_loader();

//EOF
