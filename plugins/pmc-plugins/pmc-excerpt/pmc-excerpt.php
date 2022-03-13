<?php
/*
Plugin Name: PMC Excerpt
Description: Adds a configurable character limit to post excerpt fields, i.e. Features DEK, Post Excerpt or Field Override.
Version: 1.0
Author: Kelin Chauhan <kelin.chauhan@rtcamp.com>
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_EXCERPT_ROOT', __DIR__ );
define( 'PMC_EXCERPT_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

define( 'PMC_EXCERPT_VERSION', '1.0' );

require_once PMC_EXCERPT_ROOT . '/classes/class-pmc-excerpt.php';

function pmc_excerpt_loader() {

	/*
	 * Initialize PMC_Excerpt
	 */
	PMC\PMC_Excerpt\PMC_Excerpt::get_instance();

}

pmc_excerpt_loader();
