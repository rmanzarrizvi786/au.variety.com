<?php
/**
 * Class for extending admin settings for apple news
 *
 * @since   2021-02-23
 *
 * @package pmc-apple-news-notification
 */

namespace PMC\Apple_News_Notification;

class Admin_Apple_Spin_Settings extends \Admin_Apple_Settings_Section {

	/**
	 * Slug of the post types settings section.
	 *
	 * @var string
	 */
	protected $slug = 'spin-options';

	/**
	 * Constructor.
	 */
	function __construct() {
		$this->name = __( 'Additional Options', 'pmc-apple-news-notification' );

		$settings = [
			'utm_code_domains'                          => [
				'label'       => __( 'Append UTM codes to all linked URLs from these domains:', 'pmc-apple-news-notification' ),
				'type'        => 'string',
				'description' => __( 'Separate the domains with commas.', 'pmc-apple-news-notification' ),
			],
			'allow_sending_notifications_to_apple_news' => [
				'label'       => __( 'Allow sending Apple News Notifications. ', 'pmc-apple-news-notification' ),
				'type'        => [ 'yes', 'no' ],
				'description' => __( 'Select yes if you want to be able to send notifications to apple news via the post editor.', 'pmc-apple-news-notification' ),
			],
		];

		$groups = [
			'spin_settings'         => [
				'label'    => __( 'Link Settings', 'pmc-apple-news-notification' ),
				'settings' => [ 'utm_code_domains' ],
			],
			'notification_settings' => [
				'label'    => __( 'Notification Settings', 'pmc-apple-news-notification' ),
				'settings' => [ 'allow_sending_notifications_to_apple_news' ],
			],
		];

		$this->settings = $settings;
		$this->groups   = $groups;

		parent::__construct( 'pgm-publish-options' );
	}
}
