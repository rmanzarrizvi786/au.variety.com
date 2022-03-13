<?php
/*
Plugin Name: PMC Apple News Notification
Version: 1.0.0
Author: SpinMedia, PMC
Description: Extends Apple News plugin to add notification feature.
License: PMC Proprietary. All rights reserved.
Text Domain: pmc-apple-news-notification
*/

define( 'PMC_APPLE_NEWS_NOTIFICATION_DIR', plugin_dir_path( __FILE__ ) );
define( 'PMC_APPLE_NEWS_NOTIFICATION_URL', plugin_dir_url( __FILE__ ) );
define( 'PMC_APPLE_NEWS_NOTIFICATION_VERSION', '1.0.0' );

require_once PMC_APPLE_NEWS_NOTIFICATION_DIR . 'dependencies.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once PMC_APPLE_NEWS_NOTIFICATION_DIR . 'classes/class-core.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once PMC_APPLE_NEWS_NOTIFICATION_DIR . 'classes/class-admin-apple-spin-settings.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

PMC\Apple_News_Notification\Core::get_instance();
