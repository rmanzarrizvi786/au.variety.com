<?php
/**
 * This class is responsible to valid, store, and retrieve all ET plugin related configurations
 *
 * Authors: PMC, hvong@pmc.com
 */

namespace PMC\Exacttarget;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Options;

class Config {
	use Singleton;

	// Defined the related constant values to be used by this plugin
	const OPTION_GROUP = 'pmc_exacttarget_config';

	private $_option = null;

	protected function __construct() {
		$this->_option = PMC_Options::get_instance( self::OPTION_GROUP );
	}

	/**
	 * Bulk update API configuration values
	 * @param array $params The associate array of configuration name => value pairs to update
	 * @return $this
	 */
	public function update( array $params ) : self {

		foreach ( $params as $key => $value ) {
			$this->set( $key, $value );
		}

		// If the enabled flag is not set or empty, we want to trigger the ET Client object to recreate
		if ( empty( $params['disabled'] ) || true !== $params['disabled'] ) {
			Api::get_instance()->get_client( [], true );
		}

		return $this;

	}

	/**
	 * Set a single configuration $name => $value pair
	 * @param string $name
	 * @param $value
	 * @return $this
	 */
	public function set( string $name, $value ) : self {
		// @TODO: Possible add validation code before saving to pmc option
		$this->_option->update_option( $name, $value );
		return $this;
	}

	/**
	 * Retrieve the single configuration value for the given name
	 * @param string $name  The name of the configuration to retrieve
	 * @param bool $default The default values if option value is empty or not set
	 * @return mixed|string|bool
	 */
	public function get( string $name, $default = false ) {
		$value = $this->_option->get_option( $name );
		if ( empty( $value ) ) {

			// v1 data migration: backward compatible to use legacy app api
			switch ( $name ) {
				case 'supported_post_types':
					$value = get_option( 'sailthru_supported_post_types' );
					if ( ! empty( $value ) ) {
						$this->set( 'supported_post_types', $value );
					}
					return $value;
					break;
			}

			return $default;
		}
		return $value;
	}

	/**
	 * Return the related API configuration for ET_Client
	 * @return array
	 */
	public function api() : array {
		$default = [
			'account_id'    => false,
			'key'           => false,
			'secret'        => false,
			'disabled'      => false,
			'base_auth_url' => false,
			'base_soap_url' => false,
			'base_url'      => false,
			'legacy_app'    => false,
		];
		$values  = [];
		foreach ( $default as $key => $default ) {
			$values[ $key ] = $this->get( $key, $default );
		}

		// v1 data migration: backward compatible to use legacy app api
		if ( empty( $values['key'] ) && empty( $values['secret'] ) && empty( $values['legacy_app'] ) ) {
			$api_key    = get_option( 'sailthru_api_key' );
			$api_secret = get_option( 'sailthru_secret' );
			if ( ! empty( $api_key ) && ! empty( $api_secret ) ) {
				$values['key']        = $api_key;
				$values['secret']     = $api_secret;
				$values['legacy_app'] = true;
				$this->set( 'key', $api_key );
				$this->set( 'secret', $api_secret );
				$this->set( 'legacy_app', true );
			}
		}

		return $values;
	}

}
