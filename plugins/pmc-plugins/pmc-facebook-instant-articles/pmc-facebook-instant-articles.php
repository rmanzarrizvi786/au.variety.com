<?php
/*
Plugin Name: PMC Facebook Instant Articles
Plugin URI: https://www.pmc.com
Description: Adds custom modification to the FB IA HMTL markup as required by PMC
Version: 1.0
Author: Archana Mandhare, PMC
License: PMC Proprietary. All rights reserved.
*/

use PMC\Facebook_Instant_Articles\Analytics;
use PMC\Facebook_Instant_Articles\Boomerang;
use PMC\Facebook_Instant_Articles\Fix_Lazy_Image;
use PMC\Facebook_Instant_Articles\JW_Player;
use PMC\Facebook_Instant_Articles\Modifier;
use PMC\Facebook_Instant_Articles\Plugin;
use PMC\Facebook_Instant_Articles\Video_Embed;

define( 'PMC_FACEBOOK_INSTANT_ARTICLES_ROOT', __DIR__ );

define( 'PMC_FACEBOOK_INSTANT_ARTICLES_VERSION', '1.0' );

function pmc_fbia_loader() {
	Plugin::get_instance();
	Modifier::get_instance();
	Boomerang::get_instance();
	JW_Player::get_instance();
	Video_Embed::get_instance();
	Analytics::get_instance();
	Fix_Lazy_Image::get_instance();

	// IMPORTANT: Need to load the plugin after theme setup to allow dependencies have a chance to load, eg. co-authors-plus
	pmc_load_plugin( 'facebook-instant-articles', false, '4.0' );
}

add_action( 'after_setup_theme', 'pmc_fbia_loader' );
