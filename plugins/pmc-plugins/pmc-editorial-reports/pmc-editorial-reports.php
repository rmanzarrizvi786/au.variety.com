<?php
/*
Plugin Name: PMC Editorial Reports
Description: Generates reports for editorial staff
Version: 2.1
Author: PMC, Amit Gupta
License: PMC Proprietary.  All rights reserved.
*/
define( 'PMC_EDITORIAL_REPORTS_DIR', __DIR__ );


wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

require_once( __DIR__ . '/class-pmc-editorial-reports.php' );
require_once( __DIR__ . '/class-pmc-editorial-reports-admin.php' );

PMC_Editorial_Reports_Admin::get_instance();


// Load CLI.
// Don't need to load migration commands on production.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( __DIR__ . '/wp-cli/classes/class-editorial-reports-wp-cli.php' );
}

//EOF
