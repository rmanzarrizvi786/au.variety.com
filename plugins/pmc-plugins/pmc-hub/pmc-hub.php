<?php
/*
Plugin Name: PMC Hub
Plugin URI: http://www.pmc.com
Description: Add a “Hub” post type
Version: 1.0
Author: PMC Team
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_HUB_TEMPLATE_PATH', __DIR__ . '/templates' );

/**
 * Only add code to initialize the plugin here
 */
function pmc_hub_init_plugin() {
	\PMC\Hub\Post_Type::get_instance();
}

pmc_hub_init_plugin();
