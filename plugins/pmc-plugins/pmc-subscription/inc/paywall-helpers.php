<?php

/**
 * Get paywall configuration by environment
 *
 * @change 2015-08-07 Corey Gilmore Run $config through `wwd_paywall_config` filter
 *
 */
function pmc_paywall_get_config( $version = false ) {
	static $config = array();

	if ( ! isset( $config[ $version ] ) ) {
		$uls_url            = \PMC\Uls\Plugin::get_instance()->uls_url();
		$config[ $version ] = array(
			'fast_js_host' => $uls_url,
			'paywall_host' => $uls_url,
		);

	}

	$config[ $version ]['fast_js_host'] = apply_filters( 'pmc_uls_url', rtrim( $config[ $version ]['fast_js_host'], '/' ), 'fast_js', $version );
	$config[ $version ]['paywall_host'] = apply_filters( 'pmc_uls_url', rtrim( $config[ $version ]['paywall_host'], '/' ), 'paywall_host', $version );
	$config[ $version ]                 = apply_filters( 'pmc_subscription_paywall_config', $config[ $version ], $version );
	return $config[ $version ];

}

/**
 * Localize the paywall data for JS and load up logic
 */
function pmc_paywall_scripts() {

	if ( ! pmc_paywall_enabled() ) {
		return false;
	}

	$user_entitlement = '';
	$wp_username      = '';
	$auth_data        = pmc_paywall_get_authentication_data();

	if ( ! empty( $auth_data['entitlement'] ) && is_array( $auth_data['entitlement'] ) ) {
		$user_entitlement = implode( ',', $auth_data['entitlement'] );
	}

	if ( 'wp' === $auth_data['provider'] ) {
		$wp_username = $auth_data['wp_username'];
	}

	wp_enqueue_script( 'jquery-cookie', PMC_SUBSCRIPTION_URI . 'assets/js/jquery-cookie.min.js', [ 'jquery' ], PMC_SUBSCRIPTION_VERSION, true );

	wp_enqueue_script( 'pmc-paywall-auth-redirect', PMC_SUBSCRIPTION_URI . 'assets/js/auth-redirect.min.js', array( 'jquery', 'jquery-cookie' ), PMC_SUBSCRIPTION_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'pmc_paywall_scripts' );

/**
 * Is the paywall enabled?
 */
function pmc_paywall_enabled() {
	// if bypass is detected, paywall should be disabled
	if ( \PMC\Uls\Session::get_instance()->is_bypass() ) {
		return false;
	}
	$c = pmc_paywall_get_config();
	return ! empty( $c );
}

/**
 * Get URL for the fast javascript IP checker, which usually runs on Node.js
 *
 * @uses pmc_paywall_get_config()
 *
 * @return string URL for the ULS server, eg https://uls.wwd.com/api/v1/fast.js
 */
function pmc_paywall_fast_js_url( $version = false ) {
	$c = pmc_paywall_get_config( $version );
	return $c['fast_js_host'] . '/api/v1/fast.js';
}

/**
 * Get URL for the paywall host
 *
 * @uses pmc_paywall_get_config()
 *
 * @return string URL for the ULS server, eg https://uls.wwd.com/
 */
function pmc_paywall_host( $version = false ) {
	$c = pmc_paywall_get_config( $version );
	return $c['paywall_host'];
}

/**
 * Can the current user view the current article?
 *
 * @param int $post_id Check this post for a possible access roadblock.
 *
 * @return boolean True when access is required, false when access is not required.
 */
function pmc_paywall_roadblock( $post_id = null ) {
	if ( empty( $post_id ) ) {
		$post_id = get_queried_object_id();
	}
	// ordered for performance
	if ( ! pmc_paywall_enabled() ) {
		return false;
	}

	if ( \PMC::is_cxense_bot() ) {
		return false;
	}

	// allow googlebots to access paywalled article.
	if ( is_google_bot() ) {
		return false;
	}

	if (
		! empty( $_GET['token'] ) && // phpcs:ignore
		intval( $post_id )
	) {
		/**
		 * Refer to documentation: https://confluence.pmcdev.io/pages/viewpage.action?pageId=42566764
		 */
		$given_token    = sanitize_key( $_GET['token'] ); // phpcs:ignore
		$timestamp      = strtotime( get_the_date( 'Y-m-d H:i:s', $post_id ) );
		$uls_key        = \PMC\Uls\Plugin::get_instance()->uls_key();
		$uls_secret     = \PMC\Uls\Plugin::get_instance()->uls_secret();
		$expected_token = md5( (string) $post_id . $uls_key . $uls_secret . (string) $timestamp );
		$end_date       = date( 'F d, Y', strtotime( '-10 days' ) );

		// Before permitting access, we verify token and check that publish date was within 10 days.
		if (
			$given_token === $expected_token &&
			$timestamp >= strtotime( $end_date )
		) {
			return false;
		}
	}

	if ( pmc_paywall_user_has_entitlements( 'archive' ) ) {
		return false;
	}

	$post_type       = get_post_type( $post_id );
	$free_post_types = apply_filters( 'pmc_subscription_paywall_free_post_types', [] );

	if ( false !== $post_type && in_array( $post_type, (array) $free_post_types, true ) ) {
		return false;
	}

	$required_level = pmc_paywall_article_access_required( $post_id );

	if ( empty( $required_level ) || pmc_paywall_user_has_entitlements( $required_level ) ) {
		return false;
	}

	return true;
}

/**
 * To check if current request is from 'Googlebot'.
 *
 * Verifies if user agent string has substring as Googlebot or Googlebot-News.
 *
 * @return bool
 */
function is_google_bot(): bool {

	if ( function_exists( 'vary_cache_on_function' ) ) {
		vary_cache_on_function( is_google_bot_function_string() );
	}

	return (bool) preg_match( '/Googlebot|Googlebot-News/i', PMC::filter_input( INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING ) );
}

function is_google_bot_function_string(): string {
	return 'return (bool) preg_match( "/Googlebot|Googlebot-News/i", $_SERVER["HTTP_USER_AGENT"] );';
}

/**
 * Return true/false if that type of content can be paywalled, or not.
 * The function should only check content type and terms.
 *
 * @param int $post_id
 * @param string | bool $why reason for free/paid version
 *
 * @return bool
 *
 */
function pmc_paywall_is_content_eligible_for_paywall( $post_id = null, &$why = false ) {

	if ( empty( $post_id ) ) {
		$queried_object = get_queried_object();
		if ( $queried_object instanceof WP_Post || $queried_object instanceof WP_Post_Type ) {
			if ( is_post_type_viewable( $queried_object->post_type ) ) {
				$post_id = get_queried_object_id();
			}
		}
	}

	if ( empty( $post_id ) ) {
		$why = 'not_a_post';
		return false;
	}

	// If it's not an article or content type, it's not eligible for paywall.
	$post_type = get_post_type( $post_id );
	if ( false === $post_type ) {
		$why = 'not_a_post';
		return false;
	}

	$free_post_types = apply_filters( 'pmc_subscription_paywall_free_post_types', [] );
	if ( ! empty( $free_post_types ) && is_array( $free_post_types ) ) {
		if ( in_array( $post_type, (array) $free_post_types, true ) ) {
			$why = 'free_content_type';

			return false;
		}
	}

	$free_terms = apply_filters( 'pmc_subscription_paywall_free_terms', [] );
	if ( ! empty( $free_terms ) && is_array( $free_terms ) ) {
		foreach ( $free_terms as $tax => $terms ) {

			// if terms is empty, is_object_in_term will return true if $tax exists.
			// We do not want to give away free access if we remove all free terms access.
			if ( empty( $terms ) ) {
				continue;
			}

			// is_object_in_term can potentially return wp_error object
			if ( true === is_object_in_term( $post_id, $tax, $terms ) ) {
				$why = 'free_term';

				return false;
			}

		}
	}

	return true;
}

/**
 * Return access_required in string format.
 * Possible values are 'none', 'free', 'archive', 'year'
 *
 * @param int $post_id
 * @param string | bool $why reason for free/paid version
 *
 * @return string
 *
 */
function pmc_paywall_article_access_required_string( $post_id = null, &$why = false ) {

	if ( false === pmc_paywall_is_content_eligible_for_paywall( $post_id, $why ) ) {
		return 'none';
	} else {
		$paywalled_access_type = pmc_paywall_article_access_required( $post_id, $why );
		if ( false === $paywalled_access_type ) {
			return 'free';
		} else {
			return $paywalled_access_type;
		}
	}

}

/**
 * Get access level for an article.
 * This only renders on post and content type pages, and isn't used more than once per page load.
 *
 * @param int $post_id
 * @param string | bool $why reason for free/paid version
 *
 * @return false|string cache group
 */
function pmc_paywall_article_access_required( $post_id = null, &$why = false ) {

	if ( empty( $post_id ) ) {
		$queried_object = get_queried_object();
		if ( $queried_object instanceof WP_Post || $queried_object instanceof WP_Post_Type ) {
			if ( is_post_type_viewable( $queried_object->post_type ) ) {
				$post_id = get_queried_object_id();
			}
		}
	}

	if ( ! pmc_paywall_is_content_eligible_for_paywall( $post_id, $why ) ) {
		return false;
	}

	// Is the "free" box checked?
	$free_status = get_post_meta( $post_id, 'free', true );
	if ( 'Y' === $free_status ) {
		$why = 'meta_free';
		return false;
	}

	// If the free box isn't checked and the article is older than a year, archive access is required.
	$created = get_the_time( 'U', $post_id );
	if ( $created < time() - ( YEAR_IN_SECONDS ) ) {
		$why = 'post_age';
		return 'archive';
	}

	// Perform a generic filterable eligibility requirement
	// NOTE: This is only a temporary bandaid while we transition off WWD
	// Once that migration is complete we'll refactor this plugin with an intuitive
	// and configurable way to define per-LOB paywall rules.
	$why = apply_filters( 'pmc_subscription_paywall_generic_post_eligibility', false, $post_id );
	if ( $why ) {
		return false;
	}

	$why = 'default_permission';
	return 'year';
}

/**
 * Determine if a user is logged into the paywall
 *
 * Account for both manually-logged-in users and
 * auto-logged-in users (via their IP)
 *
 * @return bool True when the user is logged-in, false otherwise.
 */
function pmc_paywall_is_user_logged_in() {

	if ( pmc_paywall_user_has_entitlements( [ 'year', 'archive', 'digital-daily' ], 'or' ) ) {
		return true;
	}

	return false;
}

/**
 * Disern if the current user has certain entitlement(s).
 *
 * @param string|array $propsective_entitlement The entitlement to check for, e.g.
 *                                              'archive'
 *                                              array( 'archive' )
 *                                              array( 'archive', 'digital-daily' )
 * @param string       $comparison              The comparison to use, defaults to 'and'.
 *                                              Possible values: 'and', 'or'.
 *                                              E.g. does the user have 'archive' and 'digital-daily'
 *                                              E.g. does the user have 'archive' or 'digital-daily'
 *
 * @return bool True when the user has the entitlement(s).
 *              False when the user does not have the entitlement(s).
 *              False on failure.
 */
function pmc_paywall_user_has_entitlements( $propsective_entitlements = '', $comparison = 'and' ) {

	if ( 'and' === $comparison ) {
		return \PMC\Uls\Session::get_instance()->can_access_all( $propsective_entitlements );
	} else {
		return \PMC\Uls\Session::get_instance()->can_access_any( $propsective_entitlements );
	}

}

/**
 * Gather data about the user's current session in the Paywall.
 *
 * @param bool $force Defaults to false.
 *                    Set to true to recache static variables.
 *
 * @return false|array False on failure.
 *                     An array of user authentication data success, e.g.
 *                     Array(
 *                         'provider'    => 'cds|ip' (possibly others in the future)
 *                         'entitlements => Array( 'archive', 'digital-daily' ),
 *                     )
 */
function pmc_paywall_get_authentication_data( $force = false ) {

	$auth_data = \PMC\Uls\Plugin::get_instance()->get_authentication_data( $force );

	if ( $auth_data ) {
		return (array) $auth_data;
	}

	return false;

}

/**
 * Locate and return X number of paragraphs from a given string.
 *
 * @param int $limit            The number of paragraphs you'd like to get back.
 * @param string $post_content  The post content to extract from.
 *                              If none provided, defaults to get_the_content().
 *
 * @return array An array of paragraph text. Note, <p> tags are not included.
 *               [
 *                  0 => 'paragraph 1',
 *                  1 => 'paragraph 2',
 *                  2 => 'paragraph 3',
 *                  ....
 *               ]
 */
function pmc_paywall_get_roadblock_teaser_copy( $limit = 2, $post_content = '' ) {

	$paragraphs = [];

	if ( empty( $post_content ) ) {
		$post_content = get_the_content();
	}

	if ( $post_content ) {
		$post_content = strip_shortcodes( $post_content );
		$post_content = apply_filters( 'the_content', $post_content );

		// Trim trailing spaces from the post content lines
		$post_content = trim( preg_replace( '/\s\s+/', '', $post_content ) );

		// Trim trailing new lines from the post content lines so we can
		// cleanly explode per </p> break point.
		$post_content = trim( preg_replace( '/\n/', '', $post_content ) );

		// Explode the set of paragrahps into one paragraph per array index.
		$paragraphs = array_filter( explode( '</p>', $post_content ) );

		// Only use the number of paragraphs up to the provided or default limit
		if ( sizeof( $paragraphs ) > $limit ) {
			$paragraphs = array_slice( $paragraphs, 0, $limit );
		}

		// Strip p tags so we return just the raw content so it can be formatted
		// as needed by the UI layer.
		for ( $i = 0; $i < sizeof( $paragraphs ); $i++ ) {
			$paragraphs[ $i ] = str_ireplace( '<p>', '', $paragraphs[ $i ] );
			$paragraphs[ $i ] = str_ireplace( '</p>', '', $paragraphs[ $i ] );
			$paragraphs[ $i ] = wp_strip_all_tags( $paragraphs[ $i ] );
		}

		// Remove empty entries and reset the array keys
		// Under no circumstance should an array with empty indecies be returned.
		$paragraphs = array_filter( $paragraphs );
		$paragraphs = array_values( $paragraphs );

		// Return the array if it has data
		if ( ! empty( $paragraphs ) && is_array( $paragraphs ) ) {
			return $paragraphs;
		}
	}

	return [];
}

// EOF
