<?php
/*
Plugin Name: PMC Social Share Bar
Description: Plugin to render the Social Share bar on the site. This includes rendering the various share icons with their click URL and click tracking for GA analytics
Version: 1.0
Author: PMC, Archana Mandhare <amandhare@pmc.com>
License: PMC Proprietary.  All rights reserved.
Text Domain: pmc-social-share-bar
Domain Path: /languages
*/

define( 'PMC_SOCIAL_SHARE_BAR_ROOT', __DIR__ );

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

function pmc_social_share_bar_init(){

	PMC\Social_Share_Bar\Admin::get_instance();
	PMC\Social_Share_Bar\Config::get_instance();
	PMC\Social_Share_Bar\Frontend::get_instance();
	PMC\Social_Share_Bar\API::get_instance();

}

pmc_social_share_bar_init();

add_action( 'plugins_loaded', function () {

	load_plugin_textdomain( 'pmc-social-share-bar', false, basename( dirname( __FILE__ ) ) . '/languages' );
} );

//EOF
