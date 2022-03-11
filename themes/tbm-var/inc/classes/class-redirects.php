<?php
/**
 * Redirects
 *
 * Set up redirects for the theme.
 *
 * @package pmc-variety-2017
 * @since   2019-07-31
 */

namespace Variety\Inc;

use PMC;
use \PMC\Global_Functions\Traits\Singleton;

class Redirects {

	use Singleton;

	/**
	 * Sets up path variable and adds hooks.
	 */
	public function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to setup listeners with WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() {
		/*
		 * Actions
		 */
		add_action( 'init', [ $this, 'maybe_redirect_bad_amp_urls' ] );
		add_action( 'template_redirect', [ $this, 'maybe_return_404' ], 20 ); // lower priority
	}

	/**
	 * This method is called on template_redirect
	 * Certain programmatic ads on mobile attempt to interface with MRAID adapter
	 * this results in `mriad.js` being appended to URLS
	 * These should return a 404 through WP: https://wordpressvip.zendesk.com/hc/en-us/requests/94947
	 *
	 */
	public function maybe_return_404() {

		$current_url = PMC::filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING );

		if ( stristr( $current_url, '/mraid.js' ) !== false ) {
			wp_die( 'Page not found.', 404 );
		}

	}

	/**
	 * BR-756 '/v/s/' prefixed to AMP URLs shared from outside of Variety.
	 * Removing that here and doing a 301 redirect.
	 */
	public function maybe_redirect_bad_amp_urls() : void {

		$search_token = '/v/s/variety.com';
		$current_uri  = \PMC::filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );
		$current_uri  = \PMC::leadingslashit( $current_uri );

		if ( empty( $current_uri ) || 0 !== strpos( $current_uri, $search_token ) ) {
			return;
		}

		$uri_to_go_to = explode( $search_token, $current_uri );
		$uri_to_go_to = array_pop( $uri_to_go_to );
		$uri_to_go_to = \PMC::leadingslashit( $uri_to_go_to );

		wp_safe_redirect( $uri_to_go_to, 301 );

		// Ignoring the next line because this stops further PHP execution.
		exit; // @codeCoverageIgnore

	}

}

//EOF
