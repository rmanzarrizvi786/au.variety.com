<?php

defined( 'ABSPATH' ) or die( 'This page may not be accessed directly.' );

function onesignal_add_async_for_script( $url ) {

	if ( false === strpos( $url, '#asyncload' ) ) {
		return $url;
	} elseif ( is_admin() ) {
		return str_replace( '#asyncload', '', $url );
	} else {
		return str_replace( '#asyncload', '', $url ) . "' async='async";
	}
}

class OneSignal_Public {
	const API_ENDPOINT_JS        = 'onesignal-api/js';
	const FILTER_USE_ENDPOINT_JS = 'onesignal_use_endpoint_js';
	const SDK_VER                = 1.2;

	/**
	 * Use a fake scope to prevent conflicts with other serviceworkers.
	 *
	 * See https://documentation.onesignal.com/docs/onesignal-service-worker-faq#picking-a-onesignal-serviceworker-scope
	 */
	const SCOPE = '/pmc-onesignal.serviceworker/';

	public function __construct() {}

	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'onesignal_header' ), 10 );

		// Need to add this action on priority 11 because enclosing function is itself hooked on init hook on priority 10, check onesignal.php.
		add_action( 'init', [ __CLASS__, 'onesignal_endpoint' ], 11 );
		add_action( 'init', [ __CLASS__, 'redirect_endpoint' ], 11 );
	}

	public static function redirect_endpoint() {
		$redirect_to = false;
		$url_path = trim( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

		if ( preg_match( '@/sdk_files/(OneSignalSDKWorker|OneSignalSDKUpdaterWorker)\.js\.php@', $url_path, $matches ) ) {
			if ( apply_filters( static::FILTER_USE_ENDPOINT_JS, true ) ) {
				switch( strtolower( $matches[1] ) ) {
					case 'onesignalsdkworker':
						$redirect_to = home_url( self::API_ENDPOINT_JS . '/worker' ) ;
						break;
					case 'onesignalsdkupdaterworker':
						$redirect_to = home_url( self::API_ENDPOINT_JS . '/updater' ) ;
						break;
				}
			} else {
				$plugin_path = trim( wp_parse_url( ONESIGNAL_PLUGIN_URL, PHP_URL_PATH ), '/' ) . $matches[0];
				if ( $plugin_path !== $url_path ) {
					$redirect_to = home_url( $plugin_path );
				}
			}

			if ( ! empty( $redirect_to ) ) {
				$redirect_to = str_replace( 'http://', 'https://', $redirect_to );
			}

			wp_safe_redirect( $redirect_to, 301 );
			exit();

		}

	}

	public static function onesignal_endpoint() {

		/**
		 * Need to intercept request for ServiceWorker and render the JS content with proper headers.
		 * This is because ServiceWorkers needs to be served from the root of the site or alternatively
		 * a header( 'Service-Worker-Allowed: /' ) should be set while serving the file.
		 *
		 * Check https://wordpressvip.zendesk.com/hc/en-us/requests/108142 for more information.
		 */
		$url_path = trim( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
		if ( self::API_ENDPOINT_JS === substr( $url_path, 0, 16 ) ) {
			header( 'Service-Worker-Allowed: ' . static::SCOPE );
			header( 'Content-Type: application/javascript' );
			header( 'X-Robots-Tag: none' );
			header( 'Cache-Control: max-age=3600' ); // Allow browser & CDN to cache this page for an hr

			echo "importScripts('https://cdn.onesignal.com/sdks/OneSignalSDKWorker.js');";
			exit;
		}
	}

	// For easier debugging of sites by identifying them as WordPress
	public static function insert_onesignal_stamp() {
		echo '<meta name="onesignal" content="wordpress-plugin"/>';
	}

	private static function valid_for_key( $key, $array ) {

		if ( array_key_exists( $key, $array ) && '' !== $array[ $key ] ) {
			return true;
		}

		return false;
	}

	public static function onesignal_header() {

		$onesignal_wp_settings = OneSignal::get_onesignal_settings();

		// We do not want to activate the plugin script if APP ID is not configured.
		if ( empty( $onesignal_wp_settings['app_id'] ) ) {
			return;
		}

		if ( array_key_exists( 'subdomain', $onesignal_wp_settings ) && '' === $onesignal_wp_settings['subdomain'] ) {

			OneSignal_Public::insert_onesignal_stamp();
		}

		add_filter( 'clean_url', 'onesignal_add_async_for_script', 11, 1 );

		if ( defined( 'ONESIGNAL_DEBUG' ) && defined( 'ONESIGNAL_LOCAL' ) ) {
			wp_register_script( 'onesignal_local_sdk', 'https://localhost:3001/sdks/OneSignalSDK.js#asyncload', array( 'jquery' ), false, true );
			wp_enqueue_script( 'onesignal_local_sdk' );
		} else {
			wp_register_script( 'onesignal_remote_sdk', 'https://cdn.onesignal.com/sdks/OneSignalSDK.js#asyncload', array( 'jquery' ), false, true );
			wp_enqueue_script( 'onesignal_remote_sdk' );
		}

		wp_enqueue_script( 'onesignal_sdk_initialize', plugin_dir_url( __FILE__ ) . 'sdk_files/init.js', array( 'jquery', 'onesignal_remote_sdk' ), self::SDK_VER, false );

		$onesignal_js_config = static::get_sdk_init_options( $onesignal_wp_settings );

		wp_localize_script(
			'onesignal_sdk_initialize',
			'oneSignalConfig',
			$onesignal_js_config
		);
	}

	/**
	 * Helper function to generate SDK initialization options based on user settings.
	 *
	 * @param array $onesignal_wp_settings User settings.
	 *
	 * @return array
	 */
	private static function get_sdk_init_options( $onesignal_wp_settings ) {

		if ( empty( $onesignal_wp_settings ) ) {
			return array();
		}

		$current_plugin_url = ONESIGNAL_PLUGIN_URL;

		if ( array_key_exists( 'subdomain', $onesignal_wp_settings ) && '' === $onesignal_wp_settings['subdomain'] ) {

			if ( false === strpos( ONESIGNAL_PLUGIN_URL, 'http://localhost' ) && false === strpos( ONESIGNAL_PLUGIN_URL, 'http://127.0.0.1' ) ) {

				$current_plugin_url = preg_replace( '/(http:\/\/)/i', 'https://', ONESIGNAL_PLUGIN_URL );
			}
		}

		if ( ! apply_filters( self::FILTER_USE_ENDPOINT_JS, false ) ) {
			$onesignal_path    = $current_plugin_url . 'sdk_files/';
			$onesignal_updater = 'OneSignalSDKUpdaterWorker.js.php';
			$onesignal_worker  = 'OneSignalSDKWorker.js.php';
		} else {
			$onesignal_path    = trailingslashit( home_url( self::API_ENDPOINT_JS ) );
			$onesignal_updater = 'updater';
			$onesignal_worker  = 'worker';
		}

		// Default values for initializing OneSignal SDK. These are overriden based on user settings.
		$default_url                 = get_home_url();
		$option_http_permission_req  = array();
		$option_welcome_notification = array( 'disable' => true );
		$option_subdomain_name       = '';
		$option_path                 = '';
		$option_safari_web_id        = '';
		$option_persistNotification  = '';
		$option_prompt_options       = array();
		$option_notify_button        = array();
		$should_initialize_sdk       = true;
		$should_prompt_auto_register = false;
		$should_use_native_prompt    = false;
		$should_use_modal_prompt     = false;
		$pmc_custom_sdk_init         = false;

		// Default URL.
		if ( self::valid_for_key( 'default_url', $onesignal_wp_settings ) ) {

			$default_url = $onesignal_wp_settings['default_url'];
		}

		// HTTP permission request.
		if ( array_key_exists( 'use_http_permission_request', $onesignal_wp_settings ) && true === $onesignal_wp_settings['use_http_permission_request'] ) {

			$option_http_permission_req = array( 'enable' => true );
		}

		// Welcome notification options.
		if ( array_key_exists( 'send_welcome_notification', $onesignal_wp_settings ) && true === $onesignal_wp_settings['send_welcome_notification'] ) {

			// Override default value.
			$option_welcome_notification = array(
				'title'   => $onesignal_wp_settings['welcome_notification_title'],
				'message' => $onesignal_wp_settings['welcome_notification_message'],
			);

			if ( array_key_exists( 'welcome_notification_url', $onesignal_wp_settings ) && '' !== $onesignal_wp_settings['welcome_notification_url'] ) {

				$option_welcome_notification['url'] = $onesignal_wp_settings['welcome_notification_url'];
			}
		}

		// Subdomain options.
		if ( self::valid_for_key( 'subdomain', $onesignal_wp_settings ) ) {

			$option_subdomain_name = $onesignal_wp_settings['subdomain'];
		} else {

			$option_path = $onesignal_path;
		}

		// Safari Web ID.
		if ( $onesignal_wp_settings['safari_web_id'] ) {

			$option_safari_web_id = $onesignal_wp_settings['safari_web_id'];
		}

		// Persist Notification.
		if ( array_key_exists( 'persist_notifications', $onesignal_wp_settings ) && 'platform-default' === $onesignal_wp_settings['persist_notifications'] ) {

			$option_persistNotification = false;

		} elseif ( array_key_exists( 'persist_notifications', $onesignal_wp_settings ) && 'yes-all' === $onesignal_wp_settings['persist_notifications'] ) {

			$option_persistNotification = true;
		}

		// Prompt Options.
		if ( array_key_exists( 'prompt_customize_enable', $onesignal_wp_settings ) && true === $onesignal_wp_settings['prompt_customize_enable'] ) {

			if ( self::valid_for_key( 'prompt_action_message', $onesignal_wp_settings ) ) {

				$option_prompt_options['actionMessage'] = $onesignal_wp_settings['prompt_action_message'];
			}

			if ( self::valid_for_key( 'prompt_example_notification_title_desktop', $onesignal_wp_settings ) ) {

				$option_prompt_options['exampleNotificationTitleDesktop'] = $onesignal_wp_settings['prompt_example_notification_title_desktop'];
			}

			if ( self::valid_for_key( 'prompt_example_notification_message_desktop', $onesignal_wp_settings ) ) {

				$option_prompt_options['exampleNotificationMessageDesktop'] = $onesignal_wp_settings['prompt_example_notification_message_desktop'];
			}

			if ( self::valid_for_key( 'prompt_example_notification_title_mobile', $onesignal_wp_settings ) ) {

				$option_prompt_options['exampleNotificationTitleMobile'] = $onesignal_wp_settings['prompt_example_notification_title_mobile'];
			}

			if ( self::valid_for_key( 'prompt_example_notification_message_mobile', $onesignal_wp_settings ) ) {

				$option_prompt_options['exampleNotificationMessageMobile'] = $onesignal_wp_settings['prompt_example_notification_message_mobile'];
			}

			if ( self::valid_for_key( 'prompt_example_notification_caption', $onesignal_wp_settings ) ) {

				$option_prompt_options['exampleNotificationCaption'] = $onesignal_wp_settings['prompt_example_notification_caption'];
			}

			if ( self::valid_for_key( 'prompt_accept_button_text', $onesignal_wp_settings ) ) {

				$option_prompt_options['acceptButtonText'] = $onesignal_wp_settings['prompt_accept_button_text'];
			}

			if ( self::valid_for_key( 'prompt_cancel_button_text', $onesignal_wp_settings ) ) {

				$option_prompt_options['cancelButtonText'] = $onesignal_wp_settings['prompt_cancel_button_text'];
			}

			if ( self::valid_for_key( 'prompt_site_name', $onesignal_wp_settings ) ) {

				$option_prompt_options['siteName'] = $onesignal_wp_settings['prompt_site_name'];
			}

			if ( self::valid_for_key( 'prompt_auto_accept_title', $onesignal_wp_settings ) ) {

				$option_prompt_options['autoAcceptTitle'] = $onesignal_wp_settings['prompt_auto_accept_title'];
			}
		}

		// Notify Button.
		if ( array_key_exists( 'notifyButton_enable', $onesignal_wp_settings ) && true === $onesignal_wp_settings['notifyButton_enable'] ) {

			$option_notify_button['enable'] = true;

			if ( self::valid_for_key( 'notifyButton_position', $onesignal_wp_settings ) ) {

				$option_notify_button['position'] = $onesignal_wp_settings['notifyButton_position'];
			}

			if ( self::valid_for_key( 'notifyButton_theme', $onesignal_wp_settings ) ) {

				$option_notify_button['theme'] = $onesignal_wp_settings['notifyButton_theme'];
			}

			if ( self::valid_for_key( 'notifyButton_size', $onesignal_wp_settings ) ) {

				$option_notify_button['size'] = $onesignal_wp_settings['notifyButton_size'];
			}

			if ( array_key_exists( 'notifyButton_showAfterSubscribed', $onesignal_wp_settings ) && true !== $onesignal_wp_settings['notifyButton_showAfterSubscribed'] ) {

				$option_notify_button['displayPredicate'] = true;
			}

			if ( array_key_exists( 'use_modal_prompt', $onesignal_wp_settings ) && true === $onesignal_wp_settings['use_modal_prompt'] ) {

				$option_notify_button['modalPrompt'] = true;
			}

			if ( array_key_exists( 'notifyButton_showcredit', $onesignal_wp_settings ) && true === $onesignal_wp_settings['notifyButton_showcredit'] ) {

				$option_notify_button['showCredit'] = true;
			} else {

				$option_notify_button['showCredit'] = false;
			}

			// Button customizations.
			if ( array_key_exists( 'notifyButton_customize_enable', $onesignal_wp_settings ) && true === $onesignal_wp_settings['notifyButton_customize_enable'] ) {

				$option_notify_button_text = array();

				if ( self::valid_for_key( 'notifyButton_tip_state_unsubscribed', $onesignal_wp_settings ) ) {

					$option_notify_button_text['tip.state.unsubscribed'] = $onesignal_wp_settings['notifyButton_tip_state_unsubscribed'];
				}

				if ( self::valid_for_key( 'notifyButton_tip_state_subscribed', $onesignal_wp_settings ) ) {

					$option_notify_button_text['tip.state.subscribed'] = $onesignal_wp_settings['notifyButton_tip_state_subscribed'];
				}

				if ( self::valid_for_key( 'notifyButton_tip_state_blocked', $onesignal_wp_settings ) ) {

					$option_notify_button_text['tip.state.blocked'] = $onesignal_wp_settings['notifyButton_tip_state_blocked'];
				}

				if ( self::valid_for_key( 'notifyButton_message_action_subscribed', $onesignal_wp_settings ) ) {

					$option_notify_button_text['message.action.subscribed'] = $onesignal_wp_settings['notifyButton_message_action_subscribed'];
				}

				if ( self::valid_for_key( 'notifyButton_message_action_resubscribed', $onesignal_wp_settings ) ) {

					$option_notify_button_text['message.action.resubscribed'] = $onesignal_wp_settings['notifyButton_message_action_resubscribed'];
				}

				if ( self::valid_for_key( 'notifyButton_message_action_unsubscribed', $onesignal_wp_settings ) ) {

					$option_notify_button_text['message.action.unsubscribed'] = $onesignal_wp_settings['notifyButton_message_action_unsubscribed'];
				}

				if ( self::valid_for_key( 'notifyButton_dialog_main_title', $onesignal_wp_settings ) ) {

					$option_notify_button_text['dialog.main.title'] = $onesignal_wp_settings['notifyButton_dialog_main_title'];
				}

				if ( self::valid_for_key( 'notifyButton_dialog_main_button_subscribe', $onesignal_wp_settings ) ) {

					$option_notify_button_text['dialog.main.button.subscribe'] = $onesignal_wp_settings['notifyButton_dialog_main_button_subscribe'];
				}

				if ( self::valid_for_key( 'notifyButton_dialog_main_button_unsubscribe', $onesignal_wp_settings ) ) {

					$option_notify_button_text['dialog.main.button.unsubscribe'] = $onesignal_wp_settings['notifyButton_dialog_main_button_unsubscribe'];
				}

				if ( self::valid_for_key( 'notifyButton_dialog_blocked_title', $onesignal_wp_settings ) ) {

					$option_notify_button_text['dialog.blocked.title'] = $onesignal_wp_settings['notifyButton_dialog_blocked_title'];
				}

				if ( self::valid_for_key( 'notifyButton_dialog_blocked_message', $onesignal_wp_settings ) ) {

					$option_notify_button_text['dialog.blocked.message'] = $onesignal_wp_settings['notifyButton_dialog_blocked_message'];
				}

				$option_notify_button['text'] = $option_notify_button_text;
			}

			if ( array_key_exists( 'notifyButton_customize_colors_enable', $onesignal_wp_settings ) && $onesignal_wp_settings['notifyButton_customize_colors_enable'] ) {

				$option_notify_button_colors = array();

				if ( self::valid_for_key( 'notifyButton_color_background', $onesignal_wp_settings ) ) {

					$option_notify_button_colors['circle.background'] = $onesignal_wp_settings['notifyButton_color_background'];
				}

				if ( self::valid_for_key( 'notifyButton_color_foreground', $onesignal_wp_settings ) ) {

					$option_notify_button_colors['circle.foreground'] = $onesignal_wp_settings['notifyButton_color_foreground'];
				}

				if ( self::valid_for_key( 'notifyButton_color_badge_background', $onesignal_wp_settings ) ) {

					$option_notify_button_colors['badge.background'] = $onesignal_wp_settings['notifyButton_color_badge_background'];
				}

				if ( self::valid_for_key( 'notifyButton_color_badge_foreground', $onesignal_wp_settings ) ) {

					$option_notify_button_colors['badge.foreground'] = $onesignal_wp_settings['notifyButton_color_badge_foreground'];
				}

				if ( self::valid_for_key( 'notifyButton_color_badge_border', $onesignal_wp_settings ) ) {

					$option_notify_button_colors['badge.bordercolor'] = $onesignal_wp_settings['notifyButton_color_badge_border'];
				}

				if ( self::valid_for_key( 'notifyButton_color_pulse', $onesignal_wp_settings ) ) {

					$option_notify_button_colors['pulse.color'] = $onesignal_wp_settings['notifyButton_color_pulse'];
				}

				if ( self::valid_for_key( 'notifyButton_color_popup_button_background', $onesignal_wp_settings ) ) {

					$option_notify_button_colors['dialog.button.background'] = $onesignal_wp_settings['notifyButton_color_popup_button_background'];
				}

				if ( self::valid_for_key( 'notifyButton_color_popup_button_background_hover', $onesignal_wp_settings ) ) {

					$option_notify_button_colors['dialog.button.background.hovering'] = $onesignal_wp_settings['notifyButton_color_popup_button_background_hover'];
				}

				if ( self::valid_for_key( 'notifyButton_color_popup_button_background_active', $onesignal_wp_settings ) ) {

					$option_notify_button_colors['dialog.button.background.active'] = $onesignal_wp_settings['notifyButton_color_popup_button_background_active'];
				}

				if ( self::valid_for_key( 'notifyButton_color_popup_button_color', $onesignal_wp_settings ) ) {

					$option_notify_button_colors['dialog.button.foreground'] = $onesignal_wp_settings['notifyButton_color_popup_button_color'];
				}

				$option_notify_button['colors'] = $option_notify_button_colors;
			}

			if ( array_key_exists( 'notifyButton_customize_offset_enable', $onesignal_wp_settings ) && true === $onesignal_wp_settings['notifyButton_customize_offset_enable'] ) {

				$option_notify_button_offset = array();

				if ( self::valid_for_key( 'notifyButton_offset_bottom', $onesignal_wp_settings ) ) {

					$option_notify_button_offset['bottom'] = $onesignal_wp_settings['notifyButton_offset_bottom'];
				}

				if ( self::valid_for_key( 'notifyButton_offset_left', $onesignal_wp_settings ) ) {

					$option_notify_button_offset['left'] = $onesignal_wp_settings['notifyButton_offset_left'];
				}

				if ( self::valid_for_key( 'notifyButton_offset_right', $onesignal_wp_settings ) ) {

					$option_notify_button_offset['right'] = $onesignal_wp_settings['notifyButton_offset_right'];
				}

				$option_notify_button['offset'] = $option_notify_button_offset;
			}
		}

		// Whether to use custom SDK Init.
		if ( $onesignal_wp_settings['use_custom_sdk_init'] ) {

			$should_initialize_sdk = false;

		} else {

			// If a filter is registered and returns false then don't initialize onesignal SDK.
			if ( ! apply_filters( 'onesignal_initialize_sdk', $onesignal_wp_settings ) ) {

				// If the filter returns "$do_initialize_sdk: true", initialize the web SDK
				$should_initialize_sdk = false;
			}

			if ( array_key_exists( 'prompt_auto_register', $onesignal_wp_settings ) && true === $onesignal_wp_settings['prompt_auto_register'] ) {

				$should_prompt_auto_register = true;
			}

			if ( array_key_exists( 'use_native_prompt', $onesignal_wp_settings ) && true === $onesignal_wp_settings['use_native_prompt'] ) {

				$should_use_native_prompt = true;
			}
		}

		// Whther to use modal prompt.
		if ( array_key_exists( 'use_modal_prompt', $onesignal_wp_settings ) && true === $onesignal_wp_settings['use_modal_prompt'] ) {

			$should_use_modal_prompt = true;
		}

		// Whether to use PMC's custom SKD initialization.
		if (
			( array_key_exists( 'pmc_custom_sdk_init', $onesignal_wp_settings ) && true === $onesignal_wp_settings['pmc_custom_sdk_init'] )
			&& false === $should_initialize_sdk
			&& false === $should_use_native_prompt
			&& false === $should_prompt_auto_register
		) {

			$pmc_custom_sdk_init = true;
		}

		// OneSignal JS initialization options. Generated based on user settings via wp-admin OneSignal menu.
		$onesignal_js_init_options = array(
			'wordpress'             => true,
			'appId'                 => $onesignal_wp_settings['app_id'],
			'httpPermissionRequest' => $option_http_permission_req,
			'welcomeNotification'   => $option_welcome_notification,
			'subdomainName'         => $option_subdomain_name,
			'path'                  => $option_path,
			'safari_web_id'         => $option_safari_web_id,
			'persistNotification'   => $option_persistNotification,
			'notifyButton'          => $option_notify_button,
		);

		$onesignal_js_init_options = array_filter( $onesignal_js_init_options ); // Remove all empty keys.

		$onesignal_js_init_options['promptOptions'] = $option_prompt_options; // Need to keep this key even if the value is empty.

		$onesignal_js_config = array(
			'default_url'                    => $default_url,
			'initOptions'                    => $onesignal_js_init_options,
			'should_initialize_sdk'          => $should_initialize_sdk,
			'should_prompt_auto_register'    => $should_prompt_auto_register,
			'should_use_native_prompt'       => $should_use_native_prompt,
			'should_use_modal_prompt'        => $should_use_modal_prompt,
			'service_worker_updater_path'    => $onesignal_updater,
			'service_worker_path'            => $onesignal_worker,
			'service_worker_scope'           => static::SCOPE,
			'should_use_pmc_custom_sdk_init' => $pmc_custom_sdk_init,
		);

		return $onesignal_js_config;
	}
}
?>
