<?php
/*
Plugin Name: Amazon Onsite
Description: Handles syndication of selected posts to the Amazon Onsite Publishing feed
Version: 1.0.0
Author: Alpha Particle
Author URI: https://www.alphaparticle.com
Author Email: keanan@alphaparticle.com
License: PMC proprietary. All rights reserved.
*/

if ( function_exists( 'wpcom_vip_load_plugin' ) ) {
	wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
}

// We use the WP_List_Table API for some of the table gen
if ( ! class_exists( 'WP_List_Table' ) && is_admin() ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
if ( ! class_exists( 'WP_Posts_List_Table' ) && is_admin() ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' );
}

if ( is_admin() ) {
	require_once __DIR__ . '/classes/class-pmc-amzn-table.php';
}

define( 'AMZN_ONSITE_PLUGIN_FILE', __FILE__ );
define( 'AMZN_ONSITE_PLUGIN_DIR', __DIR__ );

function amazon_onsite_loader() {
	PMC\Amzn_Onsite\Fields::get_instance();
	PMC\Amzn_Onsite\Admin::get_instance();
	PMC\Amzn_Onsite\Setup::get_instance();
}

amazon_onsite_loader();

//EOF
