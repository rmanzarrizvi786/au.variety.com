<?php
/*
Plugin Name: PMC Video Player
Description: [flv] shortcode parser, based on Viper's Video Quicktags
Author: Viper007Bond, PMC
Version: 7.1
License: GPLv2
*/
define( 'PMC_VIDEO_PLAYER_ROOT', __DIR__ );
define( 'PMC_VIDEO_PLAYER_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );
define( 'PMC_VIDEO_PLAYER_VERSION', '2022-2' );

function pmc_video_player_loader() {

	require_once PMC_VIDEO_PLAYER_ROOT . '/dependencies.php';

	/**
	 * Load classes.
	 */
	require_once PMC_VIDEO_PLAYER_ROOT . '/class-pmc-video-player.php';
	require_once PMC_VIDEO_PLAYER_ROOT . '/class-pmc-video-player-widget.php';

	/**
	 * Load plugin and activate shortcodes
	 *
	 * Loading the object into a global so that other plugins can
	 * interact with it as necessary
	 */
	$GLOBALS['pmc_video_player'] = PMC_Video_Player::get_instance();
	PMC\Video_Player\Video::get_instance();
	PMC\Video_Player\YTPlayer::get_instance();
	PMC\Video_Player\JWPlayer::get_instance();
	PMC\Video_Player\REST_API\JW_Player::get_instance();
	PMC\Video_Player\Video_Ads::get_instance();
	\PMC\Video_Player\Bidders\IndexExchange::get_instance();

	// Load text domain.
	add_action( 'plugins_loaded', function() {
		load_plugin_textdomain( 'pmc-video-player', false, PMC_VIDEO_PLAYER_URL . '/languages' );
	} );
}

pmc_video_player_loader();

//EOF
