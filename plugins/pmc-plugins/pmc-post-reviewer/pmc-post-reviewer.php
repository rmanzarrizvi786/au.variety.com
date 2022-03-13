<?php
/*
Plugin Name: PMC Post Reviewer
Plugin URI: https://www.pmc.com
Description: Adds a custom post review screen in wp-admin to allow reviewing of posts in read-only mode to avoid post locks.
Version: 0.1
Author: Amit Gupta, PMC
License: PMC proprietary. All rights reserved.
*/

define( 'PMC_POST_REVIEWER_ROOT', __DIR__ );
define( 'PMC_POST_REVIEWER_VERSION', '0.1' );
define( 'PMC_POST_REVIEWER_URL', plugins_url( '', __FILE__ ) );


function pmc_post_reviewer_loader() {

	/*
	 * Load dependencies
	 */
	require_once PMC_POST_REVIEWER_ROOT . '/dependencies.php';

	\PMC\Post_Reviewer\Admin_UI::get_instance();

}

pmc_post_reviewer_loader();



//EOF
