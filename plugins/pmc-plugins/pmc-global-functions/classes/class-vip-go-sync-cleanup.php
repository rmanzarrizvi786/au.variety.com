<?php
/**
 * Remove environment-specific settings after a VIP data sync.
 *
 * @package pmc-global-functions
 */

namespace PMC\Global_Functions;

use PMC\Exacttarget\Config;
use PMC\Global_Functions\Traits\Singleton;

class VIP_Go_Sync_Cleanup {
	use Singleton;

	const APPLE_NEWS_OPTION = 'apple_news_settings';

	/**
	 * PMC_VIP_Go_Sync_Cleanup constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'vip_datasync_cleanup', [ $this, 'do_cleanup' ] );
	}

	/**
	 * Handle various cleanup routines.
	 *
	 * @codeCoverageIgnore Cannot test constant or class existence, but
	 * individual methods are tested.
	 */
	public function do_cleanup(): void {
		if ( 'production' === VIP_GO_APP_ENVIRONMENT ) {
			return;
		}

		$this->_clean_apple_news();
		$this->_clean_exacttarget();
		$this->_clean_fastly();
	}

	/**
	 * Remove Apple News API credentials and related settings.
	 */
	protected function _clean_apple_news(): void {
		$options = (array) get_option( static::APPLE_NEWS_OPTION );

		$overrides = [
			'api_key'             => '',
			'api_secret'          => '',
			'api_channel'         => '',
			'api_autosync'        => 'no',
			'api_autosync_update' => 'no',
		];

		$options = array_merge( $options, $overrides );

		update_option( static::APPLE_NEWS_OPTION, $options );
	}

	/**
	 * Remove Exacttarget API credentials and related settings.
	 */
	protected function _clean_exacttarget(): void {
		// Intentionally autoloading to decrease chances we don't clean up the options.
		if ( ! class_exists( Config::class, true ) ) {
			// Cannot test without being able to unload the class.
			return; // @codeCoverageIgnore
		}

		delete_option( 'sailthru_api_key' );
		delete_option( 'sailthru_secret' );

		$overrides = [
			'legacy_app'    => 0,
			'disabled'      => 1,
			'key'           => '',
			'secret'        => '',
			'account_id'    => '',
			'base_url'      => '',
			'base_auth_url' => '',
			'base_soap_url' => '',
		];

		Config::get_instance()->update( $overrides );
	}

	/**
	 * Remove Fastly settings.
	 */
	protected function _clean_fastly(): void {
		$options = [
			'fastly_api_hostname',
			'fastly_api_key',
			'fastly_service_id',
			'fastly-settings-general',
			'fastly-settings-advanced',
			'fastly-schema-version',
		];

		foreach ( $options as $option ) {
			delete_option( $option );
		}
	}
}
