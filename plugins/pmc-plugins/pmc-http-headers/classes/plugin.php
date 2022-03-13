<?php
/**
 * This plugin implement the Content Security Policy via the HTTP Headers
 */

namespace PMC\Http_Headers;

use PMC\Global_Functions\Traits\Singleton;

// We do not allow extending this class
final class Plugin {
	use Singleton;

	// use constant in case we need to re-factor code and rename the filter to something else
	const FILTER_CONTENT_SECURITY_POLICY = 'pmc-content-security-policy';

	// We need to set this variable to private to make sure no outside class can write to this variable
	private $_headers = [];

	protected function __construct() {

		// we only need to activate the send headers on frontend
		if ( ! is_admin() ) {
			// We need these filters to run late
			add_filter( 'wp_headers', [ $this, 'filter_wp_headers' ], 9999 );
		}
	}

	public function filter_wp_headers( $headers ) : array {

		$this->_headers = [
			'Content-Security-Policy'             => [],
			'Content-Security-Policy-Report-Only' => [],
			'X-Frame-Options'                     => [],
		];

		// PMCP-209: Setup CSP headers only on SSL pages
		if ( \PMC::is_https() ) {
			/*
			 * CSP headers (https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
			 *
			 * Content-Security-Policy header is setup to upgrade https requests to https if valid
			 * Content-Security-Policy-Report-Only header is setup to send mixed content warning to report-uri
			 *
			 * This class will enable report-uri (https://report-uri.com/) for all sites when the page is loaded over HTTPS
			 * Any "mixed content warning" will be reported in an effort to clear them out and make our sites secure
			 *
			 */
			$this->_headers['Content-Security-Policy'][]             = 'upgrade-insecure-requests';
			$this->_headers['Content-Security-Policy-Report-Only'][] = "default-src data: 'unsafe-inline' 'unsafe-eval' https: blob: http://*.files.wordpress.com wss://" . wp_parse_url( get_home_url(), PHP_URL_HOST ) . '; report-uri https://pmcuri.report-uri.com/r/d/csp/reportOnly';
		}

		// Default policy
		$policy = 'deny'; // Most restricted

		// in case we need to override the policy
		$policy = apply_filters( self::FILTER_CONTENT_SECURITY_POLICY, $policy );

		// WP Customizer might set these headers, but it only support a single value
		// We need the headers to support multiple values, so we take control over these headers
		// @see https://developer.wordpress.org/reference/classes/wp_customize_manager/filter_iframe_security_headers/
		if ( isset( $headers['X-Frame-Options'] ) && 'SAMEORIGIN' === $headers['X-Frame-Options'] ) {
			$policy = 'sameorigin';
		}

		/**
		 * @see https://cheatsheetseries.owasp.org/cheatsheets/Clickjacking_Defense_Cheat_Sheet.html
		 */
		switch ( $policy ) {
			case 'deny':
				$this->_headers['Content-Security-Policy'][] = "frame-ancestors 'none'"; // single quote around 'none' is important
				$this->_headers['X-Frame-Options'][]         = 'DENY';
				break;
			case 'sameorigin':
				$this->_headers['Content-Security-Policy'][] = "frame-ancestors 'self'"; // single quote around 'self' is important
				$this->_headers['X-Frame-Options'][]         = 'SAMEORIGIN';
				break;
			case 'subdomain':
				$host = wp_parse_url( get_home_url(), PHP_URL_HOST );
				$host = str_replace( 'www.', '', $host );

				// These should cover paywall subdomain api.pmc.com
				$this->_headers['Content-Security-Policy'][] = "frame-ancestors 'self' https://*." . $host . ' https://api.pmc.com https://api.pmcdev.io';

				// @see https://tools.ietf.org/id/draft-ietf-websec-x-frame-options-12.html
				// There is no way to allow multiple domain on x frame options.
				$this->_headers['X-Frame-Options'][] = 'ALLOW-FROM https://api.pmcdev.io';
				$this->_headers['X-Frame-Options'][] = 'ALLOW-FROM https://api.pmc.com';
				$this->_headers['X-Frame-Options'][] = 'SAMEORIGIN';

				break;
		}

		if ( in_array( $policy, [ 'deny', 'sameorigin', 'subdomain' ], true ) ) {
			unset( $headers['Content-Security-Policy'] );
			unset( $headers['X-Frame-Options'] );
		}

		// We need this to prevent other filter code removing the send_headers action
		if ( ! empty( $this->_headers ) && ! has_action( 'send_headers', [ $this, 'action_send_headers' ] ) ) {
			add_action( 'send_headers', [ $this, 'action_send_headers' ], 9999 );
		}

		// IMPORTANT: we need to return the rest of headers untouched
		return (array) $headers;

	}

	/**
	 * Output our customized http headers
	 */
	public function action_send_headers() : void {

		foreach ( $this->_headers as $key => $item ) {
			foreach ( array_unique( (array) $item ) as $value ) {
				if ( is_string( $value ) || ! empty( $value ) ) {
					// @see filter_wp_headers
					// The values we got from this private $this->_headers are all add with known literal value string
					// Therefore it is safe to output these raw value here
					header( sprintf( '%s: %s', $key, $value ), false );
				}
			}
		}

	}

}
