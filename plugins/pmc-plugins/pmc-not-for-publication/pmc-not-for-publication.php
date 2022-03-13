<?php
/*
Plugin Name: PMC Not For Publication
Description: A custom post type with admin UI to store articles in advance without getting them published by accident
Version: 1.0
Author: Amit Gupta
License: PMC Proprietary.  All rights reserved.
*/

function pmc_not_for_publication_loader() {
	if ( ! is_admin() ) {
		//not wp-admin, we don't need this plugin to load anywhere else
		return;
	}

	require_once( __DIR__ . '/class-pmc-not-for-publication.php' );
	$GLOBALS['pmc_not_for_publication'] = PMC_Not_For_Publication::get_instance();
}

/*
 * load up plugin on 'init' instead of 'admin_init' as post_type must be
 * registered before 'admin_menu' is called which is called before 'admin_init'
 */
add_action( 'init', 'pmc_not_for_publication_loader', 12 );


//EOF
