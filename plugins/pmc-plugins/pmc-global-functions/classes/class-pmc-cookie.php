<?php

namespace PMC\Global_Functions\Classes;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class to set/get secure cookies, this should be used instead of using `setcookie` directly
 *
 * Class PMC_Cookie
 * @package PMC\Global_Functions\Classes
 */
class PMC_Cookie {
	use Singleton;

	private $_signed_processed_cookies   = [];
	private $_unsigned_processed_cookies = [];

	/**
	 * Constants that will be used in this class and shouldn't be modified
	 */
	const SEPARATOR = 'NULL'; // Using a NON PRINTABLE CHARACTERS for separator
	const SALT      = '1NMFIUPQmUC3oVdwvZ4TMA=/0e635da5';
	const PREFIX    = 'pmcsc_';

	/**
	 * Set cookie using a hash for the value for security purposes.
	 *
	 * @param string $name      The cookie name, e.g. 'my_cookie'
	 * @param string $value     The cookie value, e.g. 'my_value'
	 * @param int    $expire    The cookie expiration timestamp e.g. 1591226235
	 * @param string $path      The cookie path e.g. '/fashion-new'
	 * @param string $domain    The cookie domain e.g. 'wwd.com'
	 * @param bool   $secure    The cookie HTTPS secure e.g. 'true'
	 * @param bool   $http_only The cookie http only request e.g. 'true'
	 * @param string $same_site The cookie `SameSite` attribute e.g. 'Lax', 'Strict', 'None' or an empty string, which will not add the SameSite attribute to the cookie. Defaulted to "Lax" due to a bug encountered in iOs Safari where "Strict" cookies wouldn't be sent if the user logged in via Auth0.
	 *
	 * @return bool If output exists prior to calling this function, it will fail and return FALSE. If function successfully runs, it will return TRUE.
	 */
	public function set_signed_cookie( string $name, string $value, int $expire = 0, string $path = '', string $domain = '', bool $secure = false, bool $http_only = false, string $same_site = 'Lax' ) : bool {
		$name = self::PREFIX . $name;

		$hash_value = $value . self::SEPARATOR . md5( $value . self::SALT );

		// Delete processed value with the same key
		unset( $this->_signed_processed_cookies[ $name ] );
		return $this->set_cookie( $name, $hash_value, $expire, $path, $domain, $secure, $http_only, $same_site );
	}

	/**
	 * Set cookie without signed value
	 *
	 * @param string $name       The cookie name, e.g. 'my_cookie'
	 * @param string $value      The cookie value, e.g. 'my_value'
	 * @param int    $expire     The cookie expiration timestamp e.g. 1591226235
	 * @param string $path       The cookie path e.g. '/fashion-new'
	 * @param string $domain     The cookie domain e.g. 'wwd.com'
	 * @param bool   $secure     The cookie HTTPS secure e.g. 'true'
	 * @param bool   $http_only  The cookie http only request e.g. 'true'
	 * @param bool   $set_prefix The cookie prefix, e.g 'pmcsc_<my_cookie>'
	 * @param string $same_site  The cookie `SameSite` attribute e.g. 'Lax', 'Strict', 'None' or an empty string, which will not add the SameSite attribute to the cookie. Defaulted to "Lax" due to a bug encountered in iOs Safari where "Strict" cookies wouldn't be sent if the user logged in via Auth0.
	 *
	 * @return bool If output exists prior to calling this function, it will fail and return FALSE. If function successfully runs, it will return TRUE.
	 */
	public function set_unsigned_cookie( string $name, string $value, int $expire = 0, string $path = '', string $domain = '', bool $secure = false, bool $http_only = false, bool $set_prefix = true, string $same_site = 'Lax' ) : bool {
		if ( true === $set_prefix ) {
			$name = self::PREFIX . $name;
		}

		// Delete processed value with the same key
		unset( $this->_unsigned_processed_cookies[ $name ] );

		return $this->set_cookie( $name, $value, $expire, $path, $domain, $secure, $http_only, $same_site );
	}

	/**
	 * This method is a wrapper for PHP's setcookie() method with added functionality
	 * to allow unit testing setting up cookies by providing testable data on PHP Cli.
	 * Use `set_signed_cookie` method  for security purposes and mock this function
	 * for tests
	 *
	 * @param string $name      The name of the cookie.
	 * @param string $value     The value of the cookie.
	 * @param int    $expire    The time the cookie expires. This is a Unix timestamp so is in number of seconds since the epoch.
	 * @param string $path      The path on the server in which the cookie will be available on. If set to '/', the cookie will be available within the entire domain.
	 * @param string $domain    The (sub)domain that the cookie is available to.
	 * @param bool   $secure    Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client. When set to TRUE, the cookie will only be set if a secure connection exists.
	 * @param bool   $http_only When TRUE the cookie will be made accessible only through the HTTP protocol.
	 * @param string $same_site The cookie `SameSite` attribute e.g. 'Lax', 'Strict', 'None' or an empty string, which will not add the SameSite attribute to the cookie.
	 *
	 * @return bool If output exists prior to calling this function, it will fail and return FALSE. If function successfully runs, it will return TRUE.
	 *
	 * @see `set_signed_cookie()` and `set_unsigned_cookie()` above where the "SameSite" attribute is defaulted to "Lax", which is more secure than not setting anything for SameSite. For further reasoning on using Lax, see PMCS-3812, PMCS-4118 and PMCS-4127.
	 * @note Ignoring this from code coverage since we do not have a reliable way to test this at present
	 * @codeCoverageIgnore
	 */
	public function set_cookie( string $name, string $value, int $expire = 0, string $path = '', string $domain = '', bool $secure = false, bool $http_only = false, string $same_site = 'Lax' ) : bool {
		if ( php_sapi_name() !== 'cli' ) {
			/*
			 * Code is not running on PHP Cli and we are in clear.
			 * Use the PHP method and bail out.
			 */

			$options = [
				'expires'  => $expire,
				'path'     => $path,
				'domain'   => $domain,
				'secure'   => $secure,
				'httponly' => $http_only,
			];

			if ( ! empty( $same_site ) ) {
				$options['samesite'] = $same_site;
			}

			/*
			 * Ignore the below line in PHPCS because VIP ruleset flags setcookie() as it is not
			 * compatible with Batcache. This however is needed to work with CDN_Cache class
			 * which will work with Fastly and replace Batcache.
			 */
			return (bool) setcookie( $name, $value, $options );  // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
		}

		/*
		 * Code is running on PHP Cli
		 * So lets add value to $_COOKIE array and bail out
		 *
		 * Ignore the below line in PHPCS because this code will run only in CLI mode to
		 * allow for unit tests to test cookie data
		 */
		$_COOKIE[ $name ] = $value; // phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE

		return true;
	}

	/**
	 * Return the cookie value using the cookie name, if the value is malicious
	 * the function return `null` and delete the cookie.
	 *
	 * THIS METHOD SHOULD ONLY BE USED DURING UNCACHED REQUESTS, I.e. CLI, WP-Admin, AJAX, etc.
	 *
	 * YOU ARE RESPONSIBLE FOR SANITIZING THE RETURN VALUE!!!
	 *
	 * @param string $name The cookie name, e.g. 'my_cookie'
	 *
	 * @return string|null
	 */
	public function get_signed_cookie_value( string $name ) {
		$secured_name = self::PREFIX . $name;

		if ( isset( $this->_signed_processed_cookies[ $secured_name ] ) ) {
			return $this->_signed_processed_cookies[ $secured_name ];
		}

		// Validate if the cookie exist
		if ( isset( $_COOKIE[ $secured_name ] ) ) { // phpcs:ignore
			// We obtain raw cookie data to avoid any possible encoding issues.
			// Below we perform signed-cookie sanitization by validating the cookie format is protected/signed.
			// Invalid cookies are never used and are immediately deleted.
			$cookie_value   = stripslashes( $_COOKIE[ $secured_name ] ); // phpcs:ignore
			$value_and_hash = explode( self::SEPARATOR, $cookie_value );

			// if the cookie is trustworthy we are going to return the cookie value
			// else we are going to delete it and return `null`
			if ( count( $value_and_hash ) === 2 &&
				md5( $value_and_hash[0] . self::SALT ) === $value_and_hash[1]
			) {
				$this->_signed_processed_cookies[ $secured_name ] = $value_and_hash[0];

				// Each usage of this method will employ it's own data sanitization.
				// This is because here we have no idea what the data contents are and
				// wish to remain un-opinionated to avoid any possible translation issues.
				return $this->_signed_processed_cookies[ $secured_name ];
			} else {
				//Delete malicious cookie, the function expect the original cookie name, not the translated named
				$this->delete_cookie( $name );
				return null;
			}
		}

		return null;
	}

	/**
	 * Return the cookie value using the cookie name
	 *
	 * THIS METHOD SHOULD ONLY BE USED DURING UNCACHED REQUESTS, I.e. CLI, WP-Admin, AJAX, etc.
	 *
	 * YOU ARE RESPONSIBLE FOR SANITIZING THE RETURN VALUE!!!
	 *
	 * @param string $name       The cookie name, e.g. 'my_cookie'
	 * @param bool   $has_prefix The cookie prefix, e.g. 'pmcsc_<my_cookie>'
	 *
	 * @return string|null
	 */
	public function get_unsigned_cookie_value( string $name, bool $has_prefix = true ) {
		if ( true === $has_prefix ) {
			$name = self::PREFIX . $name;
		}

		if ( isset( $this->_unsigned_processed_cookies[ $name ] ) ) {
			return $this->_unsigned_processed_cookies[ $name ];
		}

		// Validate if the cookie exist
		if ( isset( $_COOKIE[ $name ] ) ) { // phpcs:ignore
			// Each usage of this method will employ it's own data sanitization.
			// This is because here we have no idea what the data contents are and
			// wish to remain un-opinionated to avoid any possible translation issues.
			$cookie_value = stripslashes( $_COOKIE[ $name ] ); // phpcs:ignore

			$this->_unsigned_processed_cookies[ $name ] = $cookie_value;
			return $this->_unsigned_processed_cookies[ $name ];
		}

		return null;
	}

	/**
	 * Delete cookie from the browser using cookie name
	 *
	 * @param string $name       The cookie name, e.g. 'cookie'
	 * @param string $path       The path on the server in which the cookie will be available on. If set to '/', the cookie will be available within the entire domain.
	 * @param string $domain     The (sub)domain that the cookie is available to.
	 * @param bool   $secure     Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client. When set to TRUE, the cookie will only be set if a secure connection exists.
	 * @param bool   $http_only  When TRUE the cookie will be made accessible only through the HTTP protocol.
	 * @param string $same_site  The cookie `SameSite` attribute e.g. 'Lax', 'Strict', 'None'
	 */
	public function delete_cookie( string $name, string $path = '/', string $domain = '', bool $secure = false, bool $http_only = false, string $same_site = 'Lax' ) {
		$name_with_prefix = self::PREFIX . $name;

		// Delete processed value with the same key
		unset( $this->_unsigned_processed_cookies[ $name_with_prefix ] );
		unset( $this->_signed_processed_cookies[ $name_with_prefix ] );

		if ( empty( $domain ) ) {
			$domain = wp_parse_url( home_url(), PHP_URL_HOST );
		}

		$this->set_unsigned_cookie( $name, '', 1, $path, $domain, $secure, $http_only, true, $same_site );
	}
}
