<?php
/**
 * phpcs:disable WordPressVIPMinimum.Performance.LowExpiryCacheTime.LowCacheTime
 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 **/

/**
 * This class is responsible to process all ET API calls
 *
 * Authors: PMC, hvong@pmc.com
 */

namespace PMC\Exacttarget;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Options;
use FuelSdk\ET_Client;

class Api {
	use Singleton;

	// Defined the related constant values to be used by this plugin
	const CACHE_GROUP  = 'pmc_exacttarget_api';
	const CACHE_KEY    = 'pmc_et_client_object';
	const OPTION_GROUP = 'pmc_exacttarget_api';
	const OPTION_KEY   = 'pmc_et_client_object';

	private $_option = null;
	private $_client = null;

	public $last_exception = null;

	protected function __construct() {
		// We're instantiate the pmc options with our own option group avoiding pmc_option_* function
		$this->_option = PMC_Options::get_instance( self::OPTION_GROUP );
	}

	/**
	 * Return true if API is active
	 * @return bool
	 */
	public function is_active() : bool {
		$config = Config::get_instance()->api();
		// If client exists and not token has not be expire, we consider api is active
		if ( ! empty( $this->_client ) && $this->_client->getTokenExpireInMinutes() > 0 ) {
			return true;
		}
		// API is not active if key/secret is not set or disabled
		if ( empty( $config['key'] ) || empty( $config['secret'] ) || ! empty( $config['disabled'] ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Determine if the ET Client object can be cache or not and how long to cache
	 * @return $this
	 */
	public function maybe_update_cache() : self {

		if ( empty( $this->_client ) ) {
			return $this;
		}

		// By default, ET token valid for 20 minutes
		$expire_in_minutes = $this->_client->getTokenExpireInMinutes();

		// If the object is about to expire, we need to re-cache for 2 minutes;
		// This will give a 2 minutes grace period to allow the object to refresh the token
		// Either by object creation trigger or from wp cron
		if ( $expire_in_minutes <= 4 ) {
			// Can't cover this code unless we dig into fuelsdk library
			wp_cache_set( self::CACHE_KEY, $this->_client, self::CACHE_GROUP, 120 ); // @codeCoverageIgnore
		} else {
			// We want to cache the object for less than 4 minutes to allow object refresh early
			// ET_CLient object would refresh the token if expiration in is less than 4 minutes
			// So caching the object longer than object token refresh not going help
			$expire_in_seconds = ( $expire_in_minutes - 4 ) * 60;
			wp_cache_set( self::CACHE_KEY, $this->_client, self::CACHE_GROUP, $expire_in_seconds );
		}
		// Save the object in pmc option to share between servers, just in case memcache got purge, etc...
		// Cron job will trigger the object to refresh from time to time to keep ET object fresh
		// Auth token exchange is expensive, so we relying on cron job to keep auth token fresh
		$this->_option->update_option( self::OPTION_KEY, $this->_client );

		return $this;
	}

	/**
	 * This function is responsible to create the ET_Client object and establish ET API Auth session
	 *
	 * @param array $config  The configuration for ET_Client, @see class PMC\Exacttarget\Config
	 * @param bool $force    To force the ET_Client to re-create and re-establish a new Auth session
	 * @return mixed|void|null
	 */
	public function get_client( array $config = [], bool $force = false ) {

		if ( ! empty( $this->_client ) && ! $force ) {
			return $this->_client;
		}

		if ( empty( $config ) ) {
			$config = Config::get_instance()->api();
		}

		if ( empty( $config['key'] ) || empty( $config['secret'] ) ) {
			return null;
		}

		$params = [
			'applicationType'         => 'server',
			'appsignature'            => 'none',
			'clientid'                => $config['key'],
			'clientsecret'            => $config['secret'],
			'sslverifypeer'           => true,
			'useOAuth2Authentication' => ( ! (bool) $config['legacy_app'] ),
		];

		if ( ! empty( $config['account_id'] ) ) {
			$params['accountId'] = $config['account_id'];
		}
		if ( ! empty( $config['base_auth_url'] ) ) {
			$params['baseAuthUrl'] = $config['base_auth_url'];
		}
		if ( ! empty( $config['base_soap_url'] ) ) {
			$params['baseSoapUrl'] = $config['base_soap_url'];
		}
		if ( ! empty( $config['base_url'] ) ) {
			$params['baseUrl'] = $config['base_url'];
		}

		try {
			$this->last_exception = null;

			$force = apply_filters( 'pmc_exacttarget_object_force', $force );

			// If not force, we will try to retrieve the object from cache / pmc options
			if ( ! $force ) {
				$this->_client = wp_cache_get( self::CACHE_KEY, self::CACHE_GROUP );
				if ( empty( $this->_client ) ) {
					$this->_client = $this->_option->get_option( self::OPTION_KEY );
				}

				// we need to validate if the object is still active since object might be stalled from pmc_get_option
				if ( ! empty( $this->_client ) && $this->_client instanceof ET_Client ) {
					try {

						// if xmlLoc not found, it mean the cached object can't be re-use
						$xml_loc = $this->_client->getXmlLoc();
						if ( ! file_exists( $xml_loc ) ) {
							// Can't cover this code without deleting the file
							$this->_client = null; // @codeCoverageIgnore
						} else {
							// save old values to detect changes
							$old_token1 = $this->_client->getRefreshToken( null );
							$old_token2 = $this->_client->getAuthToken( null );

							// Trigger a refresh to keep session alive as needed
							$this->_client->refreshToken();

							// retrieve the tokens to detect changes
							$token1 = $this->_client->getRefreshToken( null );
							$token2 = $this->_client->getAuthToken( null );

							// Token change?
							if ( $old_token1 !== $token1 || $old_token2 !== $token2 ) {
								$this->maybe_update_cache();
							}
						}

						// We're not going to be able to simulate this exception without digging into the fuelsdk library
					} catch ( \Exception $e ) { // @codeCoverageIgnore
						$this->_client = null;
					}

				} else {
					$this->_client = null;
				}

			}

			// We need to re-create object if object is invalid: empty or not instance of ET_Client
			if ( $force || empty( $this->_client ) || ! ( $this->_client instanceof ET_Client ) ) {

				$xml_loc = dirname( dirname( __FILE__ ) ) . '/library/exacttarget/etframework.wsdl.xml';
				$xml_loc = apply_filters( 'pmc_exacttarget_xmlloc', $xml_loc );

				// Don't want symlink conflict
				// Fix by VIP added on IW
				// See: https://github.com/wpcomvip/pmc-indiewire/pull/100
				if ( 0 === strpos( $xml_loc, '/chroot' ) ) {
					// We can't cover this code
					$xml_loc = substr( $xml_loc, 7 ); // @codeCoverageIgnore
				}

				if ( ! empty( $xml_loc ) ) {
					$params['xmlloc'] = $xml_loc;
				}

				$this->_client = new ET_Client( false, false, $params );
				$this->maybe_update_cache();

			}

		} catch ( \Exception $e ) {
			$this->last_exception = $e;
			return null;
		}

		return apply_filters( 'pmc_exacttarget_object', $this->_client, $force );

	}

	/**
	 * Helper for retrieving API access token from ET.
	 *
	 * @return string
	 */
	public static function get_access_token() : string {

		$et_client = self::get_instance()->get_client();

		if ( empty( $et_client ) ) {
			return '';
		}

		return $et_client->getAuthToken();
	}

	/**
	 * Helper for retrieving base url ( hostname ) for ET API endpoints.
	 *
	 * @return string
	 */
	public static function get_base_url() : string {

		$et_client = self::get_instance()->get_client();

		if ( empty( $et_client ) || empty( $et_client->baseUrl ) ) {
			return '';
		}

		return $et_client->baseUrl;
	}

}
