<?php

namespace PMC\Uls;

use CheezCapDropdownOption;
use CheezCapTextOption;
use PMC\Global_Functions\Traits\Singleton;

/**
 * This class is responsible for
 * - setup cheezcap setting
 * - initialize vary cache function
 * - expose helper function to return data from vary cache function
 */
class Plugin {
	use Singleton;

	private $_purge_queues = [];

	/**
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		add_filter( 'pmc_uls_url', [ $this, 'default_pmc_uls_url' ], 1 ); // likely to be overwritten, so set as early priority.
		add_filter( 'pmc_cheezcap_groups', [ $this, 'filter_pmc_cheezcap_groups' ] );
		add_action( 'init', [ $this, 'action_init' ] );
	}

	public function action_init() {

		if ( is_admin() ) {
			// All admin related action/filter should add here

			return;
		}

		// All non-admin related codes after this line

		add_action( 'wp_enqueue_scripts', [ $this, 'action_enqueue_scripts' ] );

		add_action( 'pmc_google_analytics_custom_dimensions_js', [ $this, 'action_pmc_google_analytics_custom_dimensions_js' ], 10, 2 );

		if ( function_exists( 'vary_cache_on_function' ) ) {
			vary_cache_on_function( $this->_get_authentication_data_function_string() );
		}

		add_action( 'wp_print_footer_scripts', [ $this, 'action_wp_print_footer_scripts' ] );

	}

	/**
	 * Programmatically set ULS URL when one is not set by prepending `uls.` to domain.
	 *
	 * @param string $url
	 * @return string
	 */
	public function default_pmc_uls_url( $url = '' ) : string {
		if ( empty( $url ) ) {
			$url = str_replace( '://', '://uls.', home_url() );
		}

		return $url;
	}

	/**
	 * Filter to add cheezcap group
	 * @param  array  $cheezcap_groups The cheezcap groups
	 * @return array                   The array with new cheezcap group
	 */
	public function filter_pmc_cheezcap_groups( $cheezcap_groups = [] ) {

		$cheezcap_options = [

			// Cheezcap option to enter ULS URL
			new CheezCapTextOption(
				'ULS URL',  // cheezecap label
				'The ULS URL',  // cheezcap description
				'pmc_uls_url', // cheezcap id
				'', // default
				false, // use textarea
				false  // validation callback
			),

			// Cheezcap options to turn on/off fast nodejs endpoint
			new CheezCapDropdownOption(
				'Fast Nodejs Endpoint',  // cheezecap label
				'User Fast Nodejs Endpoint',  // cheezcap description
				'pmc_uls_fast', // cheezcap id
				[ 0, 1 ],
				0, // Default to 1st option, 5 minutes
				[ 'Off', 'On' ]
			),

			// Cheezcap options to select pingback grace period
			new CheezCapDropdownOption(
				'ULS Ping back grace minute',  // cheezecap label
				'Number of minutes before session expired to trigger uls pingback to keep session alive',  // cheezcap description
				'pmc_ping_back_grace_minute', // cheezcap id
				[ 1, 5, 10, 15, 20, 25, 30 ],
				1, // Default to 2nd option, 5 minutes
				[ 1, 5, 10, 15, 20, 25, 30 ]
			),

		];

		// Needed for compatibility with BGR_CheezCap
		if ( class_exists( 'BGR_CheezCapGroup' ) ) {
			$cheezcap_group_class = '\BGR_CheezCapGroup';
		} else {
			$cheezcap_group_class = '\CheezCapGroup';
		}

		$cheezcap_groups[] = new $cheezcap_group_class( 'ULS', 'pmc_uls', $cheezcap_options );

		return $cheezcap_groups;
	}

	/**
	 * Function to fire during wp action enqueue_scripts
	 */
	public function action_enqueue_scripts() {

		$ping_back_grace_minute = apply_filters( 'pmc_ping_back_grace_minute', \PMC_Cheezcap::get_instance()->get_option( 'pmc_ping_back_grace_minute' ) );
		$ping_back_grace_minute = intval( $ping_back_grace_minute );
		if ( ! $ping_back_grace_minute ) {
			$ping_back_grace_minute = 5;
		}

		$fast = apply_filters( 'pmc_uls_fast', \PMC_Cheezcap::get_instance()->get_option( 'pmc_uls_fast' ) );

		// Note: Do not register with version here, script will get concatenate on VIP environment causing file modified date not detect and cached forever
		// Required VIP js concate plugin to fix to support version flag
		wp_register_script( 'pmc-uls', pmc_maybe_minify_url( 'assets/js/uls.js', __DIR__ ), [ 'jquery', 'pmc-hooks' ] );
		wp_localize_script(
			'pmc-uls', 'uls', [
				'url'                    => $this->uls_url(),
				'fast'                   => intval( $fast ),
				'cookie_prefix'          => $this->uls_cookie_prefix(),
				'ping_back_grace_minute' => $ping_back_grace_minute,
				'is_go'                  => $this->is_go(),
			]
		);
		wp_enqueue_script( 'pmc-uls' );
		wp_enqueue_style( 'pmc-uls', pmc_maybe_minify_url( 'assets/css/uls.css', __DIR__ ) );

	}

	/**
	 * Function to fire during wp action wp_print_footer_scripts
	 */
	public function action_wp_print_footer_scripts() {

		$domain = $this->uls_domain();

		if ( ! $domain ) {
			return;
		}

		// This script is a passive script. Unless we find a reason to put on header, we need this script to be in footer for page rendering performance purposes.
		// We're output a js script to set a domain cookie if the cookie already have not been set.
		$js_cookie = 'detect_cookie=js; path=/; domain=.' . $domain;
		?>
		<script type="text/javascript">
			(function(){
				try {
					if ( document.cookie.indexOf("detect_cookie") < 0 ) {
						document.cookie = <?php echo wp_json_encode( $js_cookie ); ?>;
					}
				}catch(ignore) {}
			})();
		</script>
		<?php

	}

	/**
	 * Helper function to decode entitlement and standardize the data
	 * @param  mixed $entitlement
	 * @return array
	 */
	protected function _maybe_decode_and_sanitize_entitlements( $entitlement ) {

		// we only need to check if entitlement is a string
		if ( is_string( $entitlement ) ) {
			// we need to sanitize the input since this data got from cookie
			// even though we have validated against md5 + uls secret, we can't trust the input
			$entitlement = sanitize_text_field( $entitlement );

			$decoded = json_decode( $entitlement, false );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				// We want an array list of objects
				$entitlement = (array) $decoded;
			} else {
				if ( false !== strpos( $entitlement, ',' ) ) {
					$entitlement = explode( ',', $entitlement );
				} else {
					$entitlement = [ $entitlement ];
				}
			}

		}

		return $entitlement;
	}

	/**
	 * Return the authenticated data from a WordPress authenticated session
	 * @return object
	 */
	public function get_wp_authentication_data() {
		// Allow theme to enable support for WordPress authenticated user
		$provider_options = apply_filters( 'pmc_uls_wp_provider', [] );

		if ( ! empty( $provider_options['active'] )
			&& ! empty( $provider_options['permission'] )
			&& ! empty( $provider_options['entitlement'] )
		) {

			if ( current_user_can( $provider_options['permission'] ) ) {

				$wp_user     = wp_get_current_user();
				$wp_username = $wp_user->display_name;

				if ( empty( $wp_username ) ) {
					$wp_username = $wp_user->user_firstname;
				}

				if ( empty( $wp_username ) ) {
					$wp_username = $wp_user->user_login;
				}

				return (object) [
					'provider'    => 'wp',
					'entitlement' => $this->_maybe_decode_and_sanitize_entitlements( $provider_options['entitlement'] ),
					'wp_username' => $wp_username,
				];

			}
		}

		return false;

	}

	/**
	 * Return the authenticated data with vary cache support.
	 *
	 * @param boolean $recache To force function to bypass cache, should only use for unit testing
	 *
	 * @return object
	 */
	public function get_authentication_data( $recache = false ) {

		// This filter is useful for unit testing when
		// needing to spoof different entitlements between tests
		// and requiring fresh COOKIE data to be read on each usage.
		$recache = apply_filters( 'pmc_uls_force_recache', $recache );

		// IMPORTANT: This function get call many time, we need to make sure data is statically cached
		static $function = null;
		static $data     = null;

		if ( false === $recache ) {
			if ( ! empty( $data ) ) {
				return (object) $data;
			}
			if ( null !== $data ) {
				return $data;
			}
		}

		$data = $this->get_wp_authentication_data();

		if ( ! empty( $data ) ) {
			return (object) $data;
		}

		// We use the dynamic function created for vary_cache_on_function
		if ( empty( $function ) ) {
			$function = @create_function( '', $this->_get_authentication_data_function_string() ); // @codingStandardsIgnoreLine: Ignored as part of WI-544 VIP approved in ticket https://wordpressvip.zendesk.com/hc/en-us/requests/78347
		}

		// We're using the vary cache function to return the exact same copy of data
		// This method ensured we only have a single code to work with
		$data = $function();

		if ( $data ) {
			if ( ! empty( $data['entitlement'] ) ) {
				$data['entitlement'] = $this->_maybe_decode_and_sanitize_entitlements( $data['entitlement'] );
			}
		}

		if ( ! empty( $data ) ) {
			return (object) $data;
		}

		return $data;
	}

	/**
	 * Helper function to return the ULS cookie prefix
	 * @return string
	 */
	public function uls_cookie_prefix() {
		return apply_filters( 'pmc_uls_cookie_prefix', 'uls3' );
	}

	/**
	 * Helper function to return the ULS domain
	 * This function only return sanitized valid domain
	 * @return string | false
	 */
	public function uls_domain() {

		$domain = apply_filters( 'pmc_uls_domain', wp_parse_url( home_url(), PHP_URL_HOST ) );

		if ( ! $domain ) {
			return false;
		}

		// VERY IMPORTANT: There is no validation in vary cache function,
		// we have to make sure $domain variable contains santized data and is a valid domain

		$domain = sanitize_text_field( $domain );

		// @ref: https://stackoverflow.com/questions/10306690/what-is-a-regular-expression-which-will-match-a-valid-domain-name-without-a-subd
		if ( false === preg_match( '/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,7}$/', $domain ) ) {
			return false;
		}

		return $domain;

	}

	/**
	 * Helper function to return the ULS url
	 * @return string
	 */
	public function uls_url() {
		return apply_filters( 'pmc_uls_url', \PMC_Cheezcap::get_instance()->get_option( 'pmc_uls_url' ) );
	}

	/**
	 * Helper function to return the ULS key
	 * @return string
	 */
	public function uls_key() {
		return apply_filters( 'pmc_uls_key', PMC_ULS_KEY );
	}

	/**
	 * Helper function to return the ULS secret
	 * @return string
	 */
	public function uls_secret() {
		return apply_filters( 'pmc_uls_secret', PMC_ULS_SECRET );
	}

	/**
	 * Return function string for vary_cache_on_function
	 * We don't want to expose this function source or accessible outside this class
	 * IMPORTANT: When changing this function source, make sure unit test ran successfully.
	 */
	private function _get_authentication_data_function_string() {

		$uls_cookie_prefix = $this->uls_cookie_prefix();

		// IMPORTANT, we need to validate and whitelist the prefix
		if ( ! in_array( $uls_cookie_prefix, [ 'uls', 'uls2', 'uls3' ], true ) ) {
			throw new \Exception( 'Invalid uls cookie prefix: ' . $uls_cookie_prefix );
		}

		/**
		 * IMPORTANT: We need to vary cache on the ULS validation cookies
		 * User profile information MUST not be cached nor access from server side.
		 * Use client side javascript function uls.session.get(..) to access
		 * the user information from cookies / call uls client side api
		 *
		 * IMPORTANT: vary cache CANNOT use any custom code or class implementation
		 * All vary cache function must be pure and basic PHP code.
		 *
		 * Make sure to use double quote inside the single quote string;
		 * do not use escape character against string for readability.
		 *
		 * This vary cache function must use simple php code (no wp helper functions)
		 *
		 * @see vary_cache_on_function for the list of block list keywords
		 */

		return '
			$uls_cookie_prefix = "' . $uls_cookie_prefix . '_";

			if ( empty( $_COOKIE[ $uls_cookie_prefix . "token" ] ) ) {
				return false;
			} else {
				$a = $_COOKIE[ $uls_cookie_prefix . "token" ];

				// Do not change this order of cookies in this array as it is important due to md5 has key checking for validation.
				$b = array( "entitlement", "session_start", "session_id" );
			}

			$d = array();

			foreach ( $b as $e ) {

				if ( empty( $_COOKIE[ $uls_cookie_prefix . $e ] ) ) {
					return false;
				}

				// workaround magic quotes
				$d[] = stripcslashes( $_COOKIE[ $uls_cookie_prefix . $e ] );

			}

			// ULS uses this secret key (ULS_SECRET env variable)
			// as a salt when creating the uls token value.
			$d[] = "8d4a1fc91ddb5da332618b086c08a4a9";

			if ( md5( implode( "", $d ) ) == $a ) {

				$auth_data = array();

				if ( ! empty( $_COOKIE[ $uls_cookie_prefix . "entitlement" ] ) ) {
					$auth_data["entitlement"] = stripcslashes( $_COOKIE[ $uls_cookie_prefix . "entitlement" ] );
				}

				if ( ! empty( $_COOKIE[ $uls_cookie_prefix . "provider_name" ] ) ) {
					$auth_data["provider"] = $_COOKIE[ $uls_cookie_prefix . "provider_name" ];
				}

				if ( ! empty( $_COOKIE[ $uls_cookie_prefix . "bypass" ] ) ) {
					$auth_data["bypass"] = $_COOKIE[ $uls_cookie_prefix . "bypass" ];
				}

				return $auth_data;
			}

			return false;
		';
	}

	/**
	 * Add custom dimensions to the GA/UA javascript.
	 *
	 * @uses filter::pmc_google_analytics_custom_dimensions_js
	 * @see PMC_Google_Universal_Analytics
	 *
	 * @version 2015-08-03 Corey Gilmore Initial version - PPT-4900
	 * @version 2019-03-12 Updated - PMCS-1166, PMCS-1857
	 *
	 * @param array $dimensions List of custom dimensions
	 * @param array $dimension_map Indexes of custom dimensions
	 */
	function action_pmc_google_analytics_custom_dimensions_js( $dimensions, $dimension_map ) {
		$dim_entitlement              = 'dimension' . $dimension_map['paywall-entitlement'];
		$dim_acct_type                = 'dimension' . $dimension_map['paywall-acct-type'];
		$dim_acct_id                  = 'dimension' . $dimension_map['paywall-acct-id'];
		$dim_org_id                   = 'dimension' . $dimension_map['paywall-org-id'];
		$dim_org_name                 = 'dimension' . $dimension_map['paywall-org-name'];
		$dim_auth_provider            = 'dimension' . $dimension_map['paywall-auth-provider'];
		$dim_uls_special_product_code = 'dimension' . $dimension_map['paywall-special-product-code'];
		$dim_uls_product_code         = 'dimension' . $dimension_map['paywall-product-code'];
		$dim_user_type                = 'dimension' . $dimension_map['user-type'];
		$uls_cookie_prefix            = \PMC\Uls\Plugin::get_instance()->uls_cookie_prefix() . '_';
		?>

			function uls_cookie( name ) {
				var value = document.cookie.split( <?php echo wp_json_encode( $uls_cookie_prefix ); ?> + name + '=' );
				return value.length > 1 ? decodeURIComponent( value[1].split(';')[0] ).replace( /\+/g, ' ' ) : null;
			}

			var e = uls_cookie( 'entitlement' ) || 'none', p = uls_cookie( 'provider_name' ) || 'none';
			dim[<?php echo wp_json_encode( $dim_entitlement ); ?>] = e;
			dim[<?php echo wp_json_encode( $dim_auth_provider ); ?>] = p;

			if ( p === 'ip' ) {
				dim[<?php echo wp_json_encode( $dim_acct_type ); ?>] = 'organization';
				dim[<?php echo wp_json_encode( $dim_org_id ); ?>] = uls_cookie( 'org_id' ) || uls_cookie( 'user_identifer' ) || false;
				dim[<?php echo wp_json_encode( $dim_org_name ); ?>] = uls_cookie( 'username' ) || false;
			} else {
				dim[<?php echo wp_json_encode( $dim_acct_type ); ?>] = 'individual';
				dim[<?php echo wp_json_encode( $dim_acct_id ); ?>] = uls_cookie( 'user_id' ) || uls_cookie( 'username' ) || false;
				if( p === 'cds' ) {
					dim[<?php echo wp_json_encode( $dim_uls_special_product_code ); ?>] = uls_cookie( 'spc' ) || false;
					dim[<?php echo wp_json_encode( $dim_uls_product_code ); ?>] = uls_cookie( 'product_id' ) || false;
				}
			}
			if ( dim[<?php echo wp_json_encode( $dim_user_type ); ?>] !== 'staff' && ( e !== 'none' || /reteng/.test( location.search || window.pmc && pmc.tracking._tokens.utm_campaign ) ) ) {
				dim[<?php echo wp_json_encode( $dim_user_type ); ?>] = 'subscriber';
			}

		<?php
	} // function

	/**
	 * Are we running in the go environment?
	 *
	 * @return bool
	 *
	 * @codeCoverageIgnore Pipelines runs as 'Classic' and PMC_IS_VIP_GO_SITE is false
	 */
	public function is_go(): bool {

		if ( defined( 'PMC_IS_VIP_GO_SITE' ) && true === PMC_IS_VIP_GO_SITE ) {
			return true;
		}

		return false;
	}
}
