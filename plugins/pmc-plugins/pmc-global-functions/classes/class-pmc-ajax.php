<?php

/**
 * Version: 1.0
 * Author: PMC, Hau Vong
 * License: PMC Proprietary.  All rights reserved.
 *
 * This class implement a simple ajax endpoint: /pmc-ajax/[action]/
 * To provide a framework to extend the front end ajax functionality replacing wp-admin/admin_ajax.php
 *
 * We need a way to submit ajax call that are cacheable by not using query string.
 * When ajax get method is call, the script would convert the input array of data into cache friendly url path without the query string part.
 * When ajax post method is call, all data is submit as a post form data bypass caching.
 *
 */

/**
 * Example server side source code to bind an action to a function
 *
 * PMC_Ajax::get_instance()->add_action('my-action', 'my_action_function' );
 * function my_action_function( $args ) {
 *   echo 'This is ajax result';
 *   exit();
 * });
 *
 */

/**
 * Example client javascript ajax post call.
 * NOTE: POST is not cacheable
 *
 * pmc_ajax.post({
 *    scheme: 'http',		// if pass, use http or https protocol, default use relative path
 *    action: 'my-action',	// ajax action
 *    args: {					// ajax parameters object $args to pass to server side function my_action_function( $args )
 *       param1: 'value1',
 *       param2: 'value2'
 *    },
 *    success: function(data, textStatus, jqXHR) {
 *       // do something if successful
 *    },
 *    error: function(data, textStatus, jqXHR) {
 *       // do something if there is error
 *    }
 * });
 *
 * Example client javascript ajax get call to allow caching
 *
 * pmc_ajax.get({
 *    scheme: 'http',		// if pass, use http or https protocol, default use relative path
 *    action: 'my-action',	// ajax action
 *    args: {					// ajax parameters object $args to pass to server side function my_action_function( $args )
 *       param1: 'value1',
 *       param2: 'value2'
 *    },
 *    success: function(data, textStatus, jqXHR) {
 *       // do something if successful
 *    },
 *    error: function(data, textStatus, jqXHR) {
 *       // do something if there is error
 *    }
 * });
 *
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Ajax {

	use Singleton;

	protected function __construct() {
		// register endpoint /pmc-ajax/
		add_rewrite_endpoint( 'pmc-ajax', EP_ROOT );
		add_action( 'template_redirect', array( $this, 'action_template_redirect' ) );

		// We need to use init action to add and register script to avoid doing it wrong warning
		add_action( 'init', [ $this, 'action_init' ] );

	}

	/**
	 * Init action to register scripts
	 */
	public function action_init() {
		wp_register_script( 'pmc-ajax', pmc_global_functions_url( '/js/pmc-ajax.js' ), array( 'jquery' ) );
		wp_localize_script( 'pmc-ajax', 'pmc_ajax_options', array(
			'ajax_url_https' => $this->ajax_url( 'https' ),
			'ajax_url_http'  => $this->ajax_url( 'http' ),
			'ajax_url'       => $this->ajax_url( 'relative' ),
		) );
		wp_enqueue_script( 'pmc-ajax' );
	}

	/**
	 * Use template redirect action to process the ajax action hook
	 */
	public function action_template_redirect() {
		global $wp_query;

		if ( empty( $wp_query->query_vars['pmc-ajax'] ) ) {
			return;
		}

		// Cross domain support
		if ( !empty( $_SERVER['HTTP_ORIGIN'] ) ) {
			// Method must be refactored to be testable due to use of `exit`.
			$origin = sanitize_text_field( $_SERVER['HTTP_ORIGIN'] ); // @codeCoverageIgnore
			// only allow origin that is from same host
			// @TODO: need a white list to allow crossdomain origin
			if ( parse_url( $origin, PHP_URL_HOST ) == parse_url( home_url(), PHP_URL_HOST ) ) {
				header('Access-Control-Allow-Origin: ' . $origin );
			}
		}

		// each token is separate by character /
		$tokens = explode('/', $wp_query->query_vars['pmc-ajax'] ) ;
		$endpoint = array_shift( $tokens );
		// the js use dash (-) instead of equal (=) to mimic friendly url, so we need to translate back
		$tokens = array_map( function( $item ) { return preg_replace('/-/', '=', $item, 1 ); }, $tokens );
		// piece back the tokens into a querystring then use wp_parse_args to parse it into array
		$args = wp_parse_args( implode('&', $tokens ) );

		// if it's POST method, we want to merge the array so we can pass that to the function
		if ( is_array( $_POST ) ) {
			$args = array_merge( $args, $_POST );
		}

		// do ajax action with the query array $args
		do_action('pmc-ajax-'. $this->sanitize_endpoint( $endpoint ), $args );
		// we need to call exit here to stop wp from executing any more code since we're doing an ajax callback
		// note: wp_die would output some error message here, but we want clean exit.
		exit();
	}

	/**
	 * Bind an ajax action to a callable function to process the ajax action
	 * @param string $action The ajax action name
	 * @param callable $function The callable function
	 * @return void
	 */
	public function add_action( $action, $function ) {

		if ( !is_callable( $function ) ) {
			return;
		}

		add_action( 'pmc-ajax-'. $this->sanitize_endpoint( $action ), $function );
	}

	/**
	 * return the ajax url endpoint
	 * @param string $scheme Optional schema: http, https, relative
	 * @return string The ajax url
	 */
	public function ajax_url( $scheme = null ) {
		return home_url( '/pmc-ajax/', $scheme );
	}

	/**
	 * restric and sanitize the endpoint characters
	 * @param string $endpoint
	 * @return string the sanitized string
	 */
	public function sanitize_endpoint( $endpoint ) {
		$endpoint = str_replace('/', '-', trim( $endpoint ) );
		return sanitize_file_name( $endpoint );
	}

}
