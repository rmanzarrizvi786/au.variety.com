<?php
/*
Plugin Name: PMC SEO Backdoor
Description: Allow editing of SEO-relevant data without opening the post/page.  This allows an SEO manager to change the meta description, meta keywords, and SEO title without interrupting the author's workflow.
Version: 2015-08-07
Author: PMC, Gabriel Koen & Mike Auteri
License: PMC Proprietary.  All rights reserved.
*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

// We use the WP_List_Table API for some of the table gen
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

include_once __DIR__ . '/classes/list-posts.php';
include_once __DIR__ . '/classes/admin.php';
include_once __DIR__ . '/classes/post-stats.php';

PMC\SEO_Backdoor\Post_Stats::get_instance();

add_action( 'init', 'pmc_seo_backdoor_loader' );

/**
 * For loading PMC_SEO_Backdoor via WordPress action
 *
 * @since 0.9.0
 * @version 0.9.0
 */
function pmc_seo_backdoor_loader() {
	if ( ! isset( $GLOBALS['pmc_seo_backdoor'] ) ) {
		$GLOBALS['pmc_seo_backdoor'] = PMC\SEO_Backdoor\Admin::get_instance();
	}
}

//EOF
