<?php
namespace PMC\NDN;

use \PMC;
use \PMC\NDN;

/**
 * Basic flow:
 * init: if current user can edit_posts, grab location, IP, detect if mobile, detect if high-risk country
 *       if high-risk country, also hook the email subject
 * NDN runs, checks IPs and User Agents, calls `ndn_run_for_current_user` with a privilege check
 * ndn_run_for_current_user: always true for anyone with edit_posts from a high-risk country
 *
 */

use \PMC\Global_Functions\Traits\Singleton;

class Notify {

	use Singleton;

	const PMC_NDN_ALERTS_EMAIL = 'dist.ndnalerts@pmc.com';
	const GRACE_PERIOD         = 4 * DAY_IN_SECONDS;
	const CACHE_KEY            = 'pmc_ndn_ip_cache_';
	const CACHE_LIFE           = 3600;

	protected $_details = array(
		'is_mobile'               => null,
		'ip'                      => null,
		'original_user_agent'     => null,
		'user_agent'              => null,
		'high_risk_country'       => null,
		'location'                => null,
		'args'                    => null,
	);

	protected $_whitelisted_ips = array(
		'38.75.3.226', // LA user traffic
		'104.207.211.214', // NY user traffic
	);

	/*
	 * Initialize the class
	 *
	 * @since 2016-06-20
	 * @version 2016-06-20 Archana Mandhare PMCVIP-1802
	 *
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/*
	 * Setup the hooks and filters
	 *
	 * @since 2016-06-20
	 * @version 2016-06-20 Archana Mandhare PMCVIP-1802
	 *
	 */
	protected function _setup_hooks() {
		add_filter( 'init', array( $this, 'init_user_details' ), 10 );
		add_filter( 'ndn_send_email_to', array( $this, 'get_send_email_to_list' ), 999999 );
		add_filter( 'ndn_cc_current_user', '__return_false' );
		add_filter( 'ndn_run_for_current_user', array( $this, 'ndn_run_for_current_user' ), 11 );
		add_filter( 'ndn_run_for_current_user', array( $this, 'ndn_always_run_for_high_risk' ), 999999 );
		add_filter( 'ndn_grace_period', array( $this, 'ndn_grace_period' ), 99 );
		add_action( 'ndn_send_email', array( $this, 'ndn_send_email' ), 10, 2 );
		add_action( 'ndn_message', array( $this, 'ndn_email_message' ), 10, 3 );
		add_filter( 'ndn_location', array( $this, 'ndn_get_default_location' ), 99 );
	}

	/*
	 * Filter to add the emails list that needs to be notified when user logs in from a new device
	 *
	 * @since 2016-06-20
	 * @version 2016-06-20 Archana Mandhare PMCVIP-1802
	 *
	 * @param $to_email array
	 *
	 * @return array
	 */
	public function get_send_email_to_list( $to_email ) {
		$to_email = array( self::PMC_NDN_ALERTS_EMAIL );
		return $to_email;
	}

	/*
	 * Filter to determine if NDN should run for the current user.
	 *
	 * @since 2016-09-26
	 * @version 2016-09-26 Corey Gilmore
	 *
	 * @param $is_privileged_user bool
	 *
	 * @return bool
	 */
	public function ndn_run_for_current_user( $is_privileged_user ) {
		/**
		 * By default $is_privileged_user excludes Super Admins.
		 * This behavior should only be for WPCOM, everywhere else Super Admin
		 * is a legitimate privilege.
		 *
		 * @see https://github.com/Automattic/vip-go-mu-plugins/pull/301
		 */
		if ( defined( 'PMC_IS_VIP_GO_SITE' ) && PMC_IS_VIP_GO_SITE ) {
			$is_privileged_user = current_user_can( 'edit_posts' );
		}

		return $is_privileged_user;
	}

	/**
	 * Return the user's computed location. Uses a filter to facilitate unit tests.
	 *
	 * @since 2016-09-29
	 * @version 2016-09-29 Corey Gilmore
	 * @version 2018-04-23 Vinod Tella READS-1168
	 *
	 * @param  string  $ip
	 *
	 * @return object location.
	 *
	 */
	public function get_user_location( $ip ) {

		$location = new class {};
		$location = apply_filters( 'ndn_location', $location );

		if ( empty( $location->country_short ) && function_exists( 'ip2location' ) ) {
			if ( class_exists( 'New_Device_Notification' ) && is_callable( 'New_Device_Notification::ip_to_city' ) ) {
				$location = \New_Device_Notification::ip_to_city( $ip );
			} else {
				$location = ip2location( $ip );
			}
		}

		$location = apply_filters( 'pmc_ndn_user_location', $location, $ip );

		return $location;
	}

	/**
	 * Return the browser's user agent. Uses a filter to facilitate unit tests.
	 *
	 * @since 2016-09-29
	 * @version 2016-09-29 Corey Gilmore
	 *
	 */
	public function get_user_agent() {
		$user_agent = isset ( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$this->_details['original_user_agent'] = $user_agent;
		$user_agent = apply_filters( 'pmc_ndn_user_agent', $user_agent );

		return $user_agent;
	}

	/**
	 * Return the user's IP. Uses a filter to facilitate unit tests.
	 *
	 * @since 2016-09-29
	 * @version 2016-09-29 Corey Gilmore
	 *
	 */
	public function get_user_ip() {
		$ip = isset ( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : false;
		$ip = apply_filters( 'pmc_ndn_user_ip', $ip );

		return $ip;
	}

	/**
	 * Initialize user details, including IP, location, high-risk country,
	 * and if on a mobile device. Only run once per request, and stored to prevent
	 * re-calculating unnecessarily.
	 *
	 * @since 2016-09-29
	 * @version 2016-09-29 Corey Gilmore
	 * @version 2018-04-23 Vinod Tella READS-1168
	 *
	 */
	public function init_user_details() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}
		$ip         = $this->get_user_ip();
		$user_agent = $this->get_user_agent();
		$cache_key  = self::CACHE_KEY . $ip;
		$pmc_cache  = new \PMC_Cache( $cache_key );

		$location = $pmc_cache->expires_in( self::CACHE_LIFE )
			->updates_with( [ $this, 'get_user_location' ], [ $ip ] )
			->get();

		$this->_details['ip']                = $ip;
		$this->_details['user_agent']        = $user_agent;
		$this->_details['location']          = $location;
		$this->_details['high_risk_country'] = $this->is_high_risk_country( $location, $ip );
		$this->_details['is_mobile']         = $this->is_mobile( $user_agent );

		// Modify the email subject for logins from high risk countries
		if ( $this->_details['high_risk_country'] ) {
			add_filter( 'ndn_subject', array( $this, 'ndn_subject_high_risk_country' ) );
		}

		return $this->_details;

	}

	/**
	 * Return the processed user details object.
	 *
	 * @since 2016-09-29
	 * @version 2016-09-29 Corey Gilmore
	 *
	 */
	public function get_user_details() {
		return $this->_details;
	}

	/**
	 * Helper function to detect if we're running on WPCOM.
	 * Necessary to ensure we only exclude Super Admins when absolutely necessary.
	 *
	 * @since 2016-09-29
	 * @version 2016-09-29 Corey Gilmore
	 *
	 */
	protected function _is_wpcom() {
		$is_wpcom = false;
		$is_go = ( defined( 'PMC_IS_VIP_GO_SITE' ) && PMC_IS_VIP_GO_SITE ) || ( defined( 'VIP_GO_ENV' ) && VIP_GO_ENV );
		/**
		 * Per the code in mu-plugins/000-vip-init.php, WPCOM_IS_VIP_ENV "always true for backwards compatibility"
		 * The code then defines it as false.
		 * https://github.com/Automattic/vip-go-mu-plugins/blob/d1020364e9d098d0385d5dd43a69c5ec484ea479/000-vip-init.php#L10
		 * We'll take a shotgun approach here.
		 */
		$is_wpcom_questionmark = ( defined( 'IS_WPCOM' ) && IS_WPCOM ) || ( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV );

		$is_wpcom = ! $is_go && $is_wpcom_questionmark;

		return $is_wpcom;
	}

	/*
	 * Always run NDN for administrators and logins from high-risk countries.
	 * Runs at a very low priority to act as a catch-all.
	 *
	 * @since 2016-09-26
	 * @version 2016-09-26 Corey Gilmore
	 *
	 * @param $is_privileged_user bool
	 *
	 * @return bool
	 */
	public function ndn_always_run_for_high_risk( $is_privileged_user ) {
		$high_risk_country = $this->_details['high_risk_country'];
		$is_wpcom = $this->_is_wpcom();

		// Do nothing for super admins on WPCOM - a11n's masquerading
		if ( $is_wpcom && is_super_admin() ) {
			return $is_privileged_user;
		}

		// Always run for admins
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		// Always run for privileged users connecting from a high-risk country
		if ( $high_risk_country && current_user_can( 'edit_posts' ) ) {
			return true;
		}

		return $is_privileged_user;
	}

	/*
	 * Set a more restrictive grace period for notifications
	 * 4 days instead of the default 7.
	 *
	 * @since 2016-09-26
	 * @version 2016-09-26 Corey Gilmore
	 *
	 * @param $grace int grace period for notifications, in seconds
	 *
	 * @return int
	 */
	public function ndn_grace_period( $grace ) {
		// Set the default grace to be the lower of 4 days and $grace
		$grace = min( self::GRACE_PERIOD, $grace );

		return $grace;
	}

	/**
	 * Set default location information
	 *
	 * @since 2016-09-26
	 * @version 2016-09-26 Corey Gilmore
	 *
	 * @uses filter::ndn_location
	 *
	 * @param $location object Location object
	 *
	 * @return object
	 */
	public function ndn_get_default_location( $location = false ) {
		$default_location = array(
			'latitude'      => '0.000000',
			'longitude'     => '0.000000',
			'country_short' => '??',
			'country_long'  => 'Unknown',
			'region'        => 'Unknown',
			'city'          => 'Unknown',
		);

		if ( empty( $location ) || ! $location instanceof \stdClass ) {
			$location = new \stdClass();
		}

		foreach ( $default_location as $key => $val ) {
			if (  ! isset( $location->$key ) ) {
				$location->$key = $default_location[$key];
			}
		}

		// From NDN
		$human = array();

		if ( ! empty( $location->city ) && '-' != $location->city )
			$human[] = $location->city;

		if ( ! empty( $location->region ) && '-' != $location->region && ( empty( $location->city ) || $location->region != $location->city ) )
			$human[] = $location->region;

		if ( ! empty( $location->country_long ) && '-' != $location->country_long )
			$human[] = $location->country_long;

		if ( ! empty( $human ) ) {
			$human = array_map( 'trim',       $human );
			$human = array_map( 'strtolower', $human );
			$human = array_map( 'ucwords',    $human );

			$location->human = implode( ', ', $human );
		} else {
			$location->human = 'Unknown';
		}
		// End NDN

		return $location;

	}

	/**
	 * Determine if a country is high-risk based on country code
	 * Simplistic, but better than nothing.
	 *
	 * @since 2016-09-26
	 * @version 2016-09-26 Corey Gilmore
	 *
	 * @todo Figure out how to shoehorn Florida in here, that place is trouble...
	 *
	 * @param $location object Location object
	 * @param $ip string IP Address
	 *
	 * @return bool Is the country considered high-risk?
	 */
	public function is_high_risk_country( $location, $ip ) {
		static $cache = array();
		$is_high_risk = false;

		$country = empty( $location->country_short ) ? '??' : $location->country_short;
		$country = strtoupper( $country );

		$cache_key = $country . $ip;

		// Cache risk classifications for the duration of the current request
		if ( isset( $cache[$cache_key] ) ) {
			return $cache[$cache_key];
		}

		// Countries where we've had the most trouble from and
		// also where we have no admins.
		$high_risk_countries = array(
			'BR', // Brazil
			'CN', // China
			'GH', // Ghana
			'HU', // Hungary
			'ID', // Indonesia
			'IQ', // Iraq
			'IR', // Iran
			'NG', // Nigeria
			'RO', // Romania
			'RU', // Russia
			'TH', // Thailand
			'TR', // Turkey
			'TW', // Taiwan
			'UA', // Ukraine
			'VN', // Vietnam
			// '??', // Unknown/unset -- disable temporarily while VIP fixes location on Go
		);

		$is_high_risk = in_array( $country, $high_risk_countries );

		$is_high_risk = apply_filters( 'pmc_is_high_risk_country', $is_high_risk, $country, $location, $ip );
		$cache[$cache_key] = $is_high_risk;

		return $is_high_risk;
	}

	/**
	 * Check if the current User Agent is mobile. Uses jetpack_is_mobile() by default.
	 * Falls back to very basic checks taken from Jetpack.
	 *
	 * @since 2016-09-26
	 * @version 2016-09-26 Corey Gilmore
	 *
	 * @return bool
	 */
	public function is_mobile( $ua, &$detection_method = false ) {
		$is_mobile = false;
		$ua = strtolower( $ua );

		if ( function_exists( 'jetpack_is_mobile' ) && apply_filters( 'pmc_ndn_is_mobile_use_jetpack', '__return_true' ) ) {
			$detection_method = 'jetpack';
			// Use the filtered the UA for Jetpack
			$_SERVER['HTTP_USER_AGENT'] = $ua;
			$is_mobile = jetpack_is_mobile();
			$_SERVER['HTTP_USER_AGENT'] = $this->_details['original_user_agent'];
		} else {
			$detection_method = 'direct';
			$ua_contains_mobile = ( false !== strpos( $ua, 'mobile' ) );
			$is_ipad = ( false !== strpos( $ua, 'ipad' ) );
			$is_ipod = ( false !== strpos( $ua, 'ipod' ) );

			$is_iphone = ! $is_ipad && ! $is_ipod && ( false !== strpos( $ua, 'iphone' ) );
			$is_blackberry = ( false !== strpos( $ua, 'blackberry' ) );
			$is_winmo = ( false !== strpos( $ua, 'windows phone' ) );
			$is_bb10 = ( false !== strpos( $ua, 'bb10' ) ) && ( false !== strpos( $ua, 'mobile' ) );
			$is_android = ( $ua_contains_mobile && ( false !== strpos( $ua, 'android' ) ) );

		 	$is_mobile = $is_iphone || $is_blackberry || $is_winmo || $is_bb10 || $is_android;
		}

		return $is_mobile;

	}
	/**
	 * Modify the email subject when a login from a high-risk country is detected.
	 * Prepends '[HIGH RISK]' to the subject for easily filtering/alerting.
	 *
	 * @since 2016-09-26
	 * @version 2016-09-26 Corey Gilmore
	 *
	 * @see New_Device_Notification::get_standard_message()
	 * @see filter::ndn_subject
	 *
	 */
	public function ndn_subject_high_risk_country( $subject ) {
		$subject = '[HIGH RISK] ' . $subject;
		return $subject;
	}

	/**
	 * Determine if a NDN email should be sent.
	 * Excludes whitelisted IPs, always runs for users in high-risk countries.
	 *
	 * @since 2016-09-29
	 * @version 2016-09-29 Corey Gilmore
	 *
	 */
	public function ndn_send_email( $send_email, $user_info ) {
		// Don't send emails for users with a whitelisted IP address
		if ( in_array( $this->_details['ip'], $this->_whitelisted_ips ) ) {
			return false;
		}

		// Always send emails for users in a high-risk country
		if (  $this->_details['high_risk_country'] ) {
			return true;
		}

		return $send_email;
	}

	/**
	 * Sanitize a WP_User object to only return public properties.
	 *
	 * @since 2016-09-30
	 * @version 2016-09-30 Corey Gilmore
	 *
	 * @param WP_User $user
	 * @return object object containing filtered/sanitized user data
	 *
	 */
	public function get_sanitized_user_data( $user ) {
		// array of property_name => default_value
		$user_fields = array(
			'ID'            => 0,
			'caps'          => array(),
			'cap_key'       => '',
			'roles'         => array(),
			'allcaps'       => array(),
			'first_name'    => 'Unknown',
			'last_name'     => 'Unknown',
			'user_login'    => 'unknown',
			'user_nicename' => 'Unknown User',
			'user_email'    => 'unknown@example.com',
			'display_name'  => 'Unknown User',
		);

		$sanitized_user = new \stdClass();
		if ( ! $user instanceof \WP_User ) {
			$user = new \stdClass();
		}

		$sanitized_user = new \stdClass();

		foreach ( $user_fields as $prop => $default ) {
			if (  isset( $user->$prop ) ) {
				$sanitized_user->$prop = $user->$prop;
			} else if ( isset( $user->data->$prop ) ) {
				$sanitized_user->$prop = $user->data->$prop;
			} else {
				$sanitized_user->$prop = $default;
			}
		}

		return $sanitized_user;
	}

	/**
	 * Customize the body of the email sent by NDN. Forked from
	 * New_Device_Notification::get_standard_message(), but includes more detailed
	 * location information to support better filtering.
	 *
	 * @since 2016-09-26
	 * @version 2016-09-26 Corey Gilmore
	 *
	 * @see New_Device_Notification::get_standard_message()
	 * @see filter::ndn_message
	 *
	 */
	public function ndn_email_message( $message, $user_obj, $args ) {
		if ( $this->_details['high_risk_country'] ) {
			$high_risk_message = 'High Risk Country: Yes';
		} else {
			$high_risk_message = '';
		}
		$is_mobile_message = $this->_details['is_mobile'] ? 'Yes' : 'No';

		$template_path = PMC_NDN_DIR . '/templates/notification-email.php';

		$sanitized_user_data = $this->get_sanitized_user_data( $user_obj );

		$message = PMC::render_template( $template_path, array(
			'message'           => $message,
			'user_obj'          => $sanitized_user_data,
			'args'              => $args,
			'user_details'      => $this->_details,
			'high_risk_message' => $high_risk_message,
			'is_mobile_message' => $is_mobile_message,
		) );

		return $message;

	}

}
