<?php
/*
Plugin Name: PMC Featured Image Backdoor
Description: Allow editing of Featured Images without opening the post/page.
Version: 0.1.0
Author: PMC, 10up, Luke Woodward
License: PMC Proprietary.  All rights reserved.
*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

// We use the WP_List_Table API for some of the table gen
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
include_once __DIR__ . '/class-pmc-multipost-thumbnail-backdoor.php';
include_once __DIR__ . '/class-pmc-featured-image-backdoor-list-posts.php';
include_once __DIR__ . '/class-pmc-featured-image-backdoor.php';

add_action( 'init', 'pmc_featured_image_backdoor_loader' );

/**
 * For loading PMC_Featured_Image_Backdoor via WordPress action
 *
 * @since 0.1.0
 * @version 0.1.0
 */
function pmc_featured_image_backdoor_loader() {
	if ( empty( $GLOBALS['pmc_featured_image_backdoor'] ) ) {
		$GLOBALS['pmc_featured_image_backdoor'] = new PMC_Featured_Image_Backdoor();
	}
}

//EOF
