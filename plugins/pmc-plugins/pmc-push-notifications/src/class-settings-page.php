<?php
/**
 * This file contains the Settings page.
 *
 * @package PMC_Push_Notifications
 */

namespace PMC\Push_Notifications;

use Fieldmanager_Group;
use Fieldmanager_TextField;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Register the Settings Page.
 */
class Settings_Page {

	use Singleton;

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		$this->register_settings_page();
	}

	/**
	 * Create the settings page, inside the "Push Notifications" menu item.
	 */
	public function register_settings_page() {
		fm_register_submenu_page(
			'push_notifications_settings',
			'edit.php?post_type=pmc-notification',
			__( 'Settings', 'pmc-push-notifications' )
		);

		add_action(
			'fm_submenu_push_notifications_settings',
			[ $this, 'settings_fields' ]
		);
	}

	/**
	 * Set up the fields.
	 *
	 * Ignoring because we don't need to retest Fieldmanager.
	 *
	 * @codeCoverageIgnore Ignoring because it was tested already.
	 */
	public function settings_fields() {
		// phpcs:disable
		$fm = new Fieldmanager_Group(
			[
				'name'           => 'push_notifications_settings',
				'label'          => __( 'Push Notifications Settings', 'pmc-push-notifications' ),
				'collapsible'    => false,
				'serialize_data' => false,
				'children'       => [
					'app_id'            => new Fieldmanager_Textfield( __( 'Application ID', 'pmc-push-notifications' ) ),
					'rest_api_key'      => new Fieldmanager_Textfield( __( 'Rest Api Key', 'pmc-push-notifications' ) ),
					'app_url'           => new Fieldmanager_Textfield( __( 'App URL', 'pmc-push-notifications' ) ),
					'included_segments' => new Fieldmanager_Textfield( __( 'Included segments. Quoted and coma separated.', 'pmc-push-notifications' ) ),
				],
			]
		);
		// phpcs:enable
		$fm->activate_submenu_page();
	}
}
