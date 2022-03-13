<?php
/*
	Plugin Name: PMC Apple News
	Plugin URI: https://pmc.com/
	Author: PMC, Archana Mandhare <amandhare@pmc.com>
	Description: This plugin is used to customize VIP Apple News plugin features as per PMC needs
	Version: 1.0
	License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_APPLE_NEWS', __DIR__ );
define( 'PMC_APPLE_NEWS_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

require_once( __DIR__ . '/dependencies.php' );

PMC\Apple_News\Content_Filter::get_instance();
PMC\Apple_News\Admin::get_instance();
PMC\Apple_News\Helper::get_instance();
PMC\Apple_News\JSON_Audit::get_instance();

if ( ! function_exists( 'is_apple_news_rendering_content' ) ) {
	/**
	 * Global method to determine if apple-news is currently rendering post content.
	 *
	 * @return bool
	 */
	function is_apple_news_rendering_content() {
		return PMC\Apple_News\Helper::get_instance()->is_rendering_content();
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'pmc-apple-news json-audit-report',
		PMC\Apple_News\WP_CLI\JSON_Audit_Report::class
	);
}

//EOF
