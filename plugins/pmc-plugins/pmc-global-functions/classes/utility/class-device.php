<?php
/**
 * Class to check for current device types
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-09-27
 */

namespace PMC\Global_Functions\Utility;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC;

class Device {

	use Singleton;

	protected $_is_mobile   = null;
	protected $_is_smart    = null;
	protected $_is_dumb     = null;
	protected $_is_tablet   = null;
	protected $_is_ipad     = null;
	protected $_is_desktop  = null;
	protected $_is_bot      = null;
	protected $_is_bot_type = [
		'alexa'            => null,
		'baiduspider'      => null,
		'bingbot'          => null,
		'cxensebot'        => null,
		'googlebot'        => null,
		'googlebot-mobile' => null,
		'googlebot-image'  => null,
		'googlebot-news'   => null,
		'msnbot'           => null,
		'outbrain'         => null,
		'pingdom.com_bot'  => null,
		'twitterbot'       => null,
	];

	/**
	 * Check if the device is a desktop based on user agent (if not tablet and not mobile, assume desktop)
	 *
	 * @return bool
	 */
	public function is_desktop() : bool {

		if ( is_bool( $this->_is_desktop ) ) {
			return $this->_is_desktop;
		}

		$this->_is_desktop = ( ! $this->is_mobile() && ! $this->is_tablet() );

		return $this->_is_desktop;

	}

	/**
	 * Check if the device is mobile, based on user agent
	 *
	 * Note, this function caches it's result in class variables,
	 * then calls itself to return the 'cache'.
	 *
	 * @param string $type The kind of device to check for. Possible values: 'any, 'smart', or 'dumb'.
	 *
	 * @return bool
	 */
	public function is_mobile( string $type = 'any' ) : bool {

		// Use cache var to avoid processing this thing again for current request
		if ( is_bool( $this->_is_mobile ) ) {

			switch ( $type ) {

				case 'smart':
					return (bool) $this->_is_smart;
					break;

				case 'dumb':
					return (bool) $this->_is_dumb;
					break;

				default:
					return $this->_is_mobile;

			}

		}

		// @since 2016-05-31: We'll need to revisit this
		if ( PMC::is_vip_go_production() ) {

			$mobile_class = PMC::filter_input( INPUT_SERVER, 'HTTP_X_MOBILE_CLASS', FILTER_SANITIZE_STRING );

			if ( ! empty( $mobile_class ) ) {

				$this->_is_smart  = ( 'smart' === $mobile_class );
				$this->_is_dumb   = ( 'dumb' === $mobile_class );
				$this->_is_mobile = ( $this->_is_smart || $this->_is_dumb );

				return $this->is_mobile( $type );

			}

		}

		if ( ! function_exists( 'jetpack_is_mobile' ) ) {
			return false;    // @codeCoverageIgnore
		}

		// always pass false - never return/cache the matched user agent
		$this->_is_mobile = ( jetpack_is_mobile( 'any', false ) ) ? true : false; // ensure we never have null
		$this->_is_smart  = ( jetpack_is_mobile( 'smart', false ) ) ? true : false; // ensure we never have null
		$this->_is_dumb   = ( jetpack_is_mobile( 'dumb', false ) ) ? true : false; // ensure we never have null

		return $this->is_mobile( $type );

	}

	/**
	 * To check whether current device is a tablet or not
	 *
	 * @return bool
	 */
	public function is_tablet() : bool {

		// Use cache var to avoid processing this thing again for current request
		if ( is_bool( $this->_is_tablet ) ) {
			return $this->_is_tablet;
		}

		// @since 2016-05-31: We'll need to revisit this
		if ( PMC::is_vip_go_production() ) {

			$mobile_class = PMC::filter_input( INPUT_SERVER, 'HTTP_X_MOBILE_CLASS', FILTER_SANITIZE_STRING );

			if ( ! empty( $mobile_class ) ) {
				$this->_is_tablet = ( 'tablet' === $mobile_class );
				return $this->_is_tablet;
			}

		}

		if ( ! class_exists( '\Jetpack_User_Agent_Info' ) || ! is_callable( '\Jetpack_User_Agent_Info::is_tablet' ) ) {
			return false;    // @codeCoverageIgnore
		}

		$this->_is_tablet = ( \Jetpack_User_Agent_Info::is_tablet() ) ? true : false;

		return $this->_is_tablet;

	}

	/**
	 * To check whether current device is an iPad or not
	 *
	 * @return bool
	 */
	public function is_ipad() : bool {

		// Use cache var to avoid processing this thing again for current request
		if ( is_bool( $this->_is_ipad ) ) {
			return $this->_is_ipad;
		}

		if ( ! class_exists( '\Jetpack_User_Agent_Info' ) || ! is_callable( '\Jetpack_User_Agent_Info::is_ipad' ) ) {
			return false;    // @codeCoverageIgnore
		}

		$this->_is_ipad = ( \Jetpack_User_Agent_Info::is_ipad() ) ? true : false;

		return $this->_is_ipad;

	}

	/**
	 * Method to check if current device is a bot or not.
	 *
	 * @param string $type Optional Type of bot to be checked - values can include googlebot, googlebot-mobile, googlebot-news, msnbot, alexa, etc.
	 *
	 * @return bool
	 */
	public function is_bot( string $type = 'any' ) : bool {

		$type = sanitize_title( $type );
		$type = ( ! array_key_exists( $type, $this->_is_bot_type ) ) ? 'any' : $type;

		if ( is_bool( $this->_is_bot ) ) {

			switch ( $type ) {

				case 'any':
					return (bool) $this->_is_bot;
					break;

				default:
					return (bool) $this->_is_bot_type[ $type ];
					break;

			}

		}

		$ua = PMC::filter_input( INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING );

		if ( ! class_exists( '\Jetpack_User_Agent_Info' ) || ! is_callable( '\Jetpack_User_Agent_Info::is_bot' ) ) {
			return false;    // @codeCoverageIgnore
		}

		$this->_is_bot = ( \Jetpack_User_Agent_Info::is_bot() ) ? true : false;

		// Cache values for all tracked bot types
		// We don't want to do this again for current request
		foreach ( $this->_is_bot_type as $bot_type => $val ) {
			$this->_is_bot_type[ $bot_type ] = ( false !== stripos( $ua, $bot_type ) );
		}

		// Return appropriate value from cache now
		return $this->is_bot( $type );

	}

}    //end class

//EOF
