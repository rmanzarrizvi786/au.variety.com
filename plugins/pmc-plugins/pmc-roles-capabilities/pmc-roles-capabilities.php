<?php
/**
 * Plugin Name: PMC Roles Capabilities
 * Description: Plugin for PMC Roles Capabilities Display
 * Version: 1.0.0
 * @package pmc-plugins
 */

namespace PMC\Roles_Capabilities;

// We use the WP_List_Table API for some of the table gen
if ( ! class_exists( 'WP_List_Table' ) && is_admin() ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

Plugin::get_instance();

//EOF
