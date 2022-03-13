<?php
/**
 * @see https://confluence.pmcdev.io/display/pmcdocs/pmc+ad+manager+conditions
 */

/*
This class is written to add support to do complex conditional checking for ad rendering
- add user_location: currently only support value: us
	to support other county, use wpcom_geo_add_location

Examples:

Register new conditional functions syntax:
PMC_Ad_Conditions::get_instance()->register(
	'name',             // required the name of the condition
	'callable function', // required a callable function
	array('p1','p1')    // optional array of function's parameters
);

Example:
PMC_Ad_Conditions::get_instance()
	->register( 'user_location', array( PMC_Ad_Conditions::get_instance(), 'user_location' ), array('geocode') )
	->register( 'is_vertical', array( 'PMC', 'is_vertical' ), array('vertical') )
	->register( 'is_paginated', 'is_paged' );

*/

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Ad_Conditions {

	use Singleton;

	protected $_hooks = array(
			'has_category' => array(
					'params'   => array('category','post'),
					'callback' => 'has_category',
				),
			'has_tag' => array(
					'params'   => array('tag','post'),
					'callback' => 'has_tag',
				),
			'has_term' => array(
					'params'   => array('term','taxonomy','post'),
					'callback' => 'has_term',
				),
			'is_page'   => array(
					'params'   => array('page'),
					'callback' => 'is_page',
				),
			'is_single' => array(
					'params'   => array('post'),
					'callback' => 'is_single',
				),
			'is_singular' => array(
					'params'   => array('post_type'),
					'callback' => 'is_singular',
				),
			'is_author' => array(
					'params' => array('author'),
					'callback' => 'is_author',
				),
			'is_category' => array(
					'params'   => array('category'),
					'callback' => 'is_category',
				),
			'is_tag' => array(
					'params'   => array('tag'),
					'callback' => 'is_tag',
				),
			'is_tax' => array(
					'params'   => array('taxonomy','term'),
					'callback' => 'is_tax',
				),
			'is_archive' => array(
					'params'   => array(),
					'callback' => 'is_archive',
				),
			'is_search' => array(
					'params'   => array(),
					'callback' => 'is_search',
				),
			'is_404' => array(
					'params'   => array(),
					'callback' => 'is_404',
				),
			'is_home' => array(
					'params'   => array(),
					'callback' => 'is_home',
				),
		);

	protected function __construct() {
		// register support for user geo location
		$this->register( 'is_country', array( $this, 'is_country' ), array('geocode'), array( 'geocode' => 'Valid values: "US" (N. America), "RU" (Russia), or "GB" (Great Britain)') );
		$this->register( 'is_vertical', array( 'PMC', 'is_vertical' ), array('vertical') );
		$this->register( 'in_vertical', array( 'PMC', 'in_vertical' ), array('vertical') );
		$this->register( 'is_post_type_archive', array( 'PMC', 'is_post_type_archive' ), array( 'post_type' ) );
		$this->register( 'is_paginated', 'is_paged' );
		$this->register( 'is_url_match', array( $this, 'is_url_match'), array( 'pattern' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );

	}

	public function action_admin_enqueue_scripts() {
		wp_enqueue_script( 'pmc-ad-condition-admin', plugins_url( 'js/pmc-ad-condition-admin.js', __FILE__ ), array( 'jquery' ) );

		$parameters = array();
		$keys = array_keys( $this->_hooks );
		sort( $keys );

		foreach ( $keys as $key ) {
			$parameters[ $key ] = $this->_hooks[ $key ]['params'];
		}

		wp_localize_script( 'pmc-ad-condition-admin', 'pmc_ad_condition_options', array( 'parameters' => $parameters ) );
	}

	/**
	 * @param string $name       Required the name of the condition function
	 * @param callable $callback Require the callable function
	 * @param array $params      Optional array list of parameters name of the function
	 * @return $this object
	 */
	public function register( $name, $callback, Array $params = array() ) {
		if ( empty( $name ) || !is_callable( $callback ) ) {
			return $this;
		}

		$this->_hooks[ $name ] = array( 'callback' => $callback, 'params' => $params );

		return $this;
	} // set

	/**
	 * @param Array $conditions The list of conditions to check
	 * @return Boolean True if one of the conditions matched
	 */
	public function is_true( $conditions, $operator = 'or' ) {
		if ( !empty( $conditions ) && is_array( $conditions ) ) {

			$return_val = false;

			foreach ( $conditions as $condition ) {
				$name = $condition['name'];

				// complex custom condition?
				if ( isset( $this->_hooks[ $name ]['callback'] ) && is_callable( $this->_hooks[ $name ]['callback'] ) ) {

					if ( $condition['result'] == call_user_func_array( $this->_hooks[ $name ]['callback'], $condition['params'] ) ) {

						if ( 'or' === $operator ) {
							return true;
						}

						$return_val = true;

					} elseif ( 'and' === $operator ) {
						return false;
					}

				}
			}

			return $return_val;
		}
		return true;
	}

	/**
	 * @param string $geocode The user geo to check
	 * @return Boolean True if location match $geocode
	 */
	public function is_country( $geocode = '') {
		return strtolower( $geocode ) == strtolower( pmc_geo_get_user_location() );
	} // function

	/**
	 * @param string $pattern The pattern to match
	 * @return Boolean True if url match $pattern
	 */
	public function is_url_match( $pattern = '' ) {
		if ( ! empty( $pattern ) ) {
			$prefix = '';
			$suffix = '';
			// We don't want to support full regular expression, so we need to apply preg_quote
			// extract the prefix to look for pattern at start of string
			if ( '^' == substr( $pattern, 0, 1 ) ) {
				$prefix = '^';
				$pattern = substr( $pattern, 1 );
			}
			// extract the suffix to look for pattern at end of string
			if ( '$' == substr( $pattern, -1, 1 ) ) {
				$suffix = '$';
				$pattern = substr( $pattern, 0, -1 );
			}
			return 1 == preg_match( '/' . $prefix . preg_quote( strtolower( $pattern ), '/' ) . $suffix . '/', strtolower( $_SERVER['REQUEST_URI'] ) );
		}
		return false;
	}

	/**
	 * @param Array $ad_conditions
	 * @return string The conditions string representing the conditions
	 */
	public function display( $ad_conditions, $data = '' ) {
		if ( !empty( $ad_conditions ) && is_array( $ad_conditions ) ) {
			$lists = array();
			foreach ( $ad_conditions as $value ) {
				$params = '';
				if ( !empty( $value['params'] ) ) {
					$params = "'" . implode("','", $value['params'] ) ."'";
				}
				$lists[]=  sprintf('%s%s(%s)', ($value['result'] ? '' : 'not '), $value['name'], $params );
			}

			$logical_condition = " OR ";
			if ( ! empty( $data['logical_operator'] ) ) {
				$logical_condition = strtoupper( " {$data['logical_operator']} " );
			}
			return implode( $logical_condition, $lists );
		}
		return '';
	}

	/**
	 * This function returns an array containing functions for use
	 * in rendering condition based ads
	 */
	public function get_condition_functions() {
		return (array) $this->_hooks;
	}


}

PMC_Ad_Conditions::get_instance();

// EOF
