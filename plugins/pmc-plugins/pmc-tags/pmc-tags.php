<?php
/*
Plugin Name: PMC Tags
Plugin URI: http://www.pmc.com
Description: General purpose tags API for adding commonly used third party tags
which can mostly be managed via the wp-admin and from a single plugin.
Version: 1.0
Author: <bcamenisch@pmc.com> Brandon Camenisch, PMC
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_TAGS_ROOT', __DIR__ );
define( 'PMC_TAGS_VERSION', '1.0' );

function pmc_tags_loader() {

	require_once PMC_TAGS_ROOT . '/dependencies.php';

	\PMC\Tags\Tags::get_instance();
	\PMC\Tags\Components\Post_Options::get_instance();

}

pmc_tags_loader();

//EOF
