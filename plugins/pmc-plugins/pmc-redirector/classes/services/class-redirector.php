<?php
/**
 * Redirection service for the PMC Redirector plugin
 * It allows registration of wildcard redirect rules and
 * performs URL redirection based on them.
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since 2017-06-19
 */

namespace PMC\Redirector\Services;


use \PMC;
use \PMC_Cache;
use PMC\Global_Functions\Traits\Singleton;

class Redirector {

	use Singleton;

	const ID = 'pmc-redirector';

	const CACHE_EXPIRY = 600;	//10 minutes

	/**
	 * @var array Regex wildcard tokens for search/match
	 */
	protected $_wildcard_tokens = array(
		'*' => '(.*)',		//match anything, zero or more
		'+' => '(.+)',		//match anything, one or more
	);

	/**
	 * @var array Wildcard Redirect Rules
	 *
	 * Don't add any redirect rules here directly, create a plugin config for
	 * pmc-redirector plugin in '<lob-theme>/plugins/config/' dir and
	 * register the wildcard redirect rules via that config.
	 */
	protected $_wildcard_rules = array();

	/**
	 * Class initialization
	 *
	 * @return void
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Method which sets up listeners to WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/*
		 * Filters
		 */
		add_filter( 'template_redirect', array( $this, 'maybe_redirect' ), 0 );	// hook in early, before the canonical redirect

	}

	/**
	 * Conditional method to check if any redirect rules have been registered or not.
	 *
	 * @return boolean Returns TRUE if redirect rules have been registered else FALSE
	 */
	protected function _has_redirect_rules() {

		return ( ! empty( $this->_wildcard_rules ) && is_array( $this->_wildcard_rules ) );

	}

	/**
	 * Conditional method to check if the passed URL is a URL path or full URL
	 *
	 * @param string $url
	 * @return boolean Returns TRUE if $url is a URL path else FALSE
	 */
	protected function _is_url_path( $url ) {

		if ( empty( $url ) || ! is_string( $url ) ) {
			return false;
		}

		if ( strpos( $url, '//' ) === 0 || strpos( $url, '://' ) !== false ) {
			return false;
		}

		return true;

	}

	/**
	 * Called on 'template_redirect' hook, this method checks whether current URL
	 * has to be redirected or not. If it does then it performs the redirect to
	 * appropriate destination.
	 *
	 * @return void
	 */
	public function maybe_redirect() {

		if ( ! $this->_has_redirect_rules() ) {
			return;
		}

		$request_uri = strtolower( $_SERVER['REQUEST_URI'] );

		$request_uri = wp_parse_url( $request_uri, PHP_URL_PATH );

		if ( ! is_string( $request_uri ) ) {
			return;
		}

		$request_uri = trim( PMC::unleadingslashit( untrailingslashit( $request_uri ) ) );

		if ( empty( $request_uri ) ) {
			// there's no URI path, so no redirect
			// that we need to bother about, bail out
			return;
		}

		$redirect_destination = $this->_get_cached_redirect_destination( sprintf( '/%s/', $request_uri ) );

		if ( empty( $redirect_destination ) || ! is_string( $redirect_destination ) ) {
			// redirect destination is empty or not a string,
			// in either case we need not bother with anything, bail out
			return;
		}

		$redirect_destination_copy = trim( str_replace( array( '/', ':' ), '', $redirect_destination ) );

		if ( empty( $redirect_destination_copy ) ) {
			// redirect destination is empty, bail out
			return;
		}

		unset( $redirect_destination_copy );

		// perform the redirect
		if ( $this->_is_url_path( $redirect_destination ) ) {
			$redirect_destination = PMC::leadingslashit( trailingslashit( $redirect_destination ) );
		}

		wp_redirect( $redirect_destination, 301 );
		exit;

	}

	/**
	 * Method to register the wildcard redirect rules for a site.
	 *
	 * The redirect rules registered here must be for wildcard matching.
	 * Simple redirects would not be accepted.
	 *
	 * @param array $wildcard_rules An array of key value pairs of wildcard redirect rules to match and their destinations
	 * @return boolean TRUE on success, FALSE on failure
	 */
	public function register_wildcard_rules( array $wildcard_rules ) {

		if ( empty( $wildcard_rules ) || count( $wildcard_rules ) < 1 ) {
			//throw exception only if current env is not production
			// translators: 1: Class name 2: Function name.
			return PMC::maybe_throw_exception( sprintf( __( '%1$s::%2$s() expects a list of wildcard rules passed as a non-empty array', 'pmc-redirector' ), get_called_class(), __FUNCTION__ ) );
		}

		$token_count = count( $this->_wildcard_tokens );

		foreach ( $wildcard_rules as $source => $destination ) {

			$missing_tokens = 0;

			foreach ( $this->_wildcard_tokens as $wildcard => $token ) {

				if ( strpos( $source, $wildcard ) === false ) {
					$missing_tokens++;
				}

			}

			if ( $missing_tokens === $token_count ) {
				return PMC::maybe_throw_exception( sprintf(
					// translators: 1: Class name 2: Function name 3: Redirect rule.
					__( '%1$s::%2$s() did not find any wildcard in the redirect rule "%3$s"', 'pmc-redirector' ),
					get_called_class(),
					__FUNCTION__,
					$source
				) );
			}

		}

		$this->_wildcard_rules = $wildcard_rules;

		return true;

	}

	/**
	 * Method to fetch redirect destination of a URL from cache
	 *
	 * @param $url
	 * @return string Redirect destination of the URL passed else empty string
	 */
	protected function _get_cached_redirect_destination( $url ) {

		$url_path = PMC::unleadingslashit( untrailingslashit( wp_parse_url( $url, PHP_URL_PATH ) ) );

		if ( empty( $url_path ) ) {

			return PMC::maybe_throw_exception(
				sprintf(
					// translators: 1: Class name 2: Function name 3: URL.
					__( '%1$s::%2$s() did not find any URI path in "%3$s"', 'pmc-redirector' ),
					get_called_class(),
					__FUNCTION__,
					$url
				),
				'ErrorException',
				''
			);

		}

		$url_path = sprintf( '/%s/', $url_path );

		$cache = new PMC_Cache( sprintf( '%s-%s', self::ID, md5( $url_path ) ) );

		return $cache->expires_in( self::CACHE_EXPIRY )
						->updates_with( [ $this, 'get_redirect_destination' ], [ $url_path ] )
						->get();

	}

	/**
	 * Method to fetch redirect destination of a URI path
	 *
	 * @param $url_path
	 * @return string Redirect destination of the URI path passed else empty string
	 */
	public function get_redirect_destination( $url_path ) {

		if ( empty( trim( $url_path, '/' ) ) ) {
			return '';
		}

		if ( ! $this->_has_redirect_rules() ) {
			return '';
		}

		foreach ( $this->_wildcard_rules as $source => $destination ) {

			$pattern = '@/' . PMC::unleadingslashit( untrailingslashit( $source ) ) . '/?@i';

			foreach ( $this->_wildcard_tokens as $wildcard => $token ) {

				$pattern = str_replace( $wildcard, $token, $pattern );

			}

			if ( preg_match( $pattern, $url_path ) ) {

				/*
				 * match found
				 * do the replacement and return
				 */
				$redirect_destination = preg_replace( $pattern, $destination, $url_path );

				return apply_filters( 'pmc-redirector-redirect-destination', $redirect_destination, $url_path );

			}

			unset( $pattern );

		}

		return '';

	}

}	//end of class


//EOF
