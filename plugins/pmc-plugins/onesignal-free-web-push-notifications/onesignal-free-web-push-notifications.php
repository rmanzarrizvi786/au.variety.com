<?php
/**
 * Plugin Name: OneSignal Push Notifications
 * Plugin URI: https://onesignal.com/
 * Description: Free web push notifications.
 * Version: 2.1.1
 * Author: OneSignal
 * Author URI: https://onesignal.com
 * License: MIT
 */

/**
 * Always use the JS endpoint as these paths are also set in OneSignal's
 * dashboard.
 */
add_filter( 'onesignal_use_endpoint_js', '__return_true' );

require_once __DIR__ . '/onesignal.php';
