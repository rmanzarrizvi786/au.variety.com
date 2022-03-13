<?php
/*
Plugin Name: PMC Video Playlist Manager
Description: This plugin creates module, that will be house the 5-10 latest videos of a selected playlist, verticals and tag pages the module will be visible in, and what timeframe will be used to determines the module’s visibility on the article pages.
Version: 1.0
Author: Jignesh Nakrani, PMC
Author URI: http://pmc.com
Author Email: jignesh.nakrani@rtcamp.com
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_VIDEO_PLAYLIST_MANAGER_ROOT', __DIR__ );

define( 'PMC_VIDEO_PLAYLIST_MANAGER_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

define( 'PMC_VIDEO_PLAYLIST_MANAGER_VERSION', '1.0' );

/**
 * Require classes.
 */
require_once( PMC_VIDEO_PLAYLIST_MANAGER_ROOT . '/class-admin.php' );
require_once( PMC_VIDEO_PLAYLIST_MANAGER_ROOT . '/class-pmc-video-playlist-frontend.php' );
require_once( PMC_VIDEO_PLAYLIST_MANAGER_ROOT . '/dependencies.php' );


/**
 * Initialize classes.
 */
PMC\PMC_Video_Playlist\PMC_Video_Playlist_Frontend::get_instance();

