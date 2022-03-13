<?php

/*
* Plugin Name: PMC JWPlayer -> YouTube Video Migration
* Plugin URI: http://pmc.com/
* Description: Provides WP-CLI interface for moving videos from JW onto YouTube
* The plugin will also override the [jwplayer] and [jwplatform] shrorcodes with
* Cheez options available in the pmc theme settings under "Video Migration".
*
* Version: 0.1
*
* Author: Brandon Camenisch, PMC
* License: PMC Proprietary.  All rights reserved
*
*/


if ( defined( 'WP_CLI' ) && WP_CLI ) {
	// Only include these files if they exist. We don't want them throwing errors
	$files = array( '/vendor/autoload.php', '/cli/pmc-wp-cli-video-migration.php' );
	foreach ( $files as $file ) {
		if ( file_exists( __DIR__ . $file ) ) {
			require_once( __DIR__ . $file );
		}
	}
	return;
}


/**
* pmc_video_migration_loader
*
*/
function pmc_video_migration_loader() {
	// Load on 
	PMC\JW_YT_Video_Migration\Cheez_Options::get_instance();

	// Load after init so we have a user object
	add_action( 'init', function() {
		if ( ! is_admin() && PMC\JW_YT_Video_Migration\Cheez_Options::is_migration_enabled() ) {
			PMC\JW_YT_Video_Migration\Post_Migration::get_instance();
		}
	} );
}

if ( function_exists( 'wpcom_vip_load_plugin' ) ) {
	wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
}

pmc_video_migration_loader();
//EOF
