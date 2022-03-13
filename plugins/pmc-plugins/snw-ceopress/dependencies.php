<?php

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
