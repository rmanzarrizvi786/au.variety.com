<?php
/**
 * Plugin Name:     PMC Push Notifications
 * Plugin URI:      https://bitbucket.org/penskemediacorp/pmc-plugins/
 * Description:     Push notifications for PMC sites
 * Author:          Alley
 * Author URI:      https://alley.co/
 * Text Domain:     pmc-push-notifications
 * Version:         0.1.0
 *
 * @package         PMC_Push_Notifications
 */

namespace PMC\Push_Notifications;

// Plugin autoloader.
require_once __DIR__ . '/src/autoload.php';

add_action( 'after_setup_theme', __NAMESPACE__ . '\loader' );

/**
 * Initialize the notifications.
 */
function loader() {
	Notification_Post_Type::get_instance();
	Settings_Page::get_instance();
};

