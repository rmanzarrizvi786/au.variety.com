<?php
/*
Plugin Name: PMC Sticky Posts
Plugin URI: http://www.pmc.com
Description: Adds a post option to mark a post as sticky post and excludes the sticky posts from home river
Version: 1.1
Author: Amit Gupta, PMC
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_STICKY_POSTS_ROOT', __DIR__ );
define( 'PMC_STICKY_POSTS_VERSION', '1.1' );


function pmc_sticky_posts_loader() {

	/*
	 * Load up plugin dependencies
	 */
	require_once PMC_STICKY_POSTS_ROOT . '/dependencies.php';

	/*
	 * Initialize PMC Sticky Posts service for homepage
	 */
	\PMC\Sticky_Posts\Service\Home::get_instance();

}

pmc_sticky_posts_loader();



//EOF