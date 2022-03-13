<?php
/*

Plugin Name: PMC Custom Endpoint Template
Plugin URI: http://www.pmc.com
Description:
	- Add ability to create an end point and assign to a custom template
	- This replace the need to create a custom page and assign it to a custom template
NOTE:
	The plugin is not self activate; this mean the end point registration should be done before
	any init hook is fired.

Version: 1
Author: PMC, Hau Vong
Author URI: http://www.pmc.com
License: PMC Proprietary. All rights reserved.

Examples:

PMC_Custom_Endpoint_Template::get_instance()-register(
		'my-new-page',    // The end point of the page
		'page-template',  // Use the template file page-template.php
		'page title',		// The page title
	);

 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Endpoint_Template {

	use Singleton;

	const MAX_ENDPOINTS = 20; // restrict number of endpoints to 20
	protected $_endpoint2template_mapping = array();
	protected $_current_endpoint = false;

	protected function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
	}

	public function action_init() {
		add_filter( 'template_include', array( $this, 'filter_template_include' ) );
		add_filter( 'wp_title', array( $this, 'filter_wp_title' ), 10, 2 );
	}

	public function filter_template_include( $template ) {
		global $wp_query;

		// custom endpoint template do not have queried object
		if ( !get_queried_object() ) {
			// search for our matching template
			foreach ( $this->_endpoint2template_mapping as $key => $map ) {
				if ( isset( $wp_query->query[ $key ] ) ) {
					$this->_current_endpoint = $key;
					// add our body class teo represent the endpoint/custom page
					pmc_add_body_class( sanitize_title_with_dashes( str_replace( '/', '-', $key ) ) );
					// since this is a custom templated endpoint/page, we should remove the the body class home
					pmc_remove_body_class( 'home' );
					add_filter( 'pmc_canonical_url', array( $this, 'filter_pmc_canonical_url' ) );
					return $map['template'];
				}
			}
		}

		// no template found, return the default
		return $template;
	}

	public function filter_pmc_canonical_url( $canonical_url ) {
		return home_url( '/' ) . trailingslashit ( $this->_current_endpoint );
	}

	/**
	 * @param string $endpoint The endpoint to register
	 * @param string $template The template to assign to the endpoint
	 * @param string $title    Optional title to give the endpoint
	 */
	public function register( $endpoint, $template, $title = '', $args = false ) {
		if ( empty( $endpoint )
			|| empty( $template )
			|| count( $this->_endpoint2template_mapping ) >= self::MAX_ENDPOINTS // enforce number of endpoints
			) {
			return;
		}

		// make sure the template is valid
		$template = locate_template( array( $template .'.php' ) );

		if ( empty( $template ) ) {
			return;
		}

		$this->_endpoint2template_mapping[ $endpoint ] = array(
				'template' => $template,
				'title' => $title,
			);

		if ( !isset( $args['paginate'] ) || true === $args['paginate'] ) {
			// We need to add custom rewrite to support pagination
			add_rewrite_rule( $endpoint . '/page/([0-9]+)?/?$','index.php?paged=$matches[1]&'. $endpoint, 'top' );
		}
		add_rewrite_endpoint( $endpoint, EP_ROOT );
	}

	/**
	 * @return string The current endpoint
	 */
	public function get_endpoint() {
		return $this->_current_endpoint;
	}

	/**
	 * @param string $default The default value if there is no endpoint title found
	 * @return string The current endpoint title
	 */
	public function get_title( $default = '' ) {
		if ( $key = $this->get_endpoint() ) {
			if ( !empty( $this->_endpoint2template_mapping[ $key ] ) ) {
				return $this->_endpoint2template_mapping[ $key ][ 'title' ];
			}
		}
		return $default;
	} // function

	public function filter_wp_title( $title, $sep = '&raquo;' ) {
		global $paged, $page;
		if ( $my_title = $this->get_title( false ) ) {
			// @see wp_title
			if ( current_theme_supports( 'title-tag' ) && ! is_feed() ) {
				$my_title .= get_bloginfo( 'name', 'display' );
				if ( $paged >= 2 || $page >= 2 ) {
					$my_title .= " $sep " . sprintf( __( 'Page %s' ), max( $paged, $page ) );
				}
			}
			return $my_title;
		}
		return $title;
	}

}

// EOF
