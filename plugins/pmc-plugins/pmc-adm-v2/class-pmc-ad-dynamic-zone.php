<?php
/*

This class is added to implement dynamic zones for google publisher ad targetting
By default, google publisher slots are rendered as:  {key}/{site}/{zone}

To add support for additional slot template, use filter pmc_adm_dynamic_slots and pmc_adm_dynamic_slot_default
To add additional replacement variable use register function.

example:

add_filter( 'pmc_adm_dynamic_slots', function( $slots ) {
	$slots['{key}/{site}/{newtag}_{zone}'] => '{key}/{site}/{newtag}_{zone}';
	return $slots;
});

add_filter( 'pmc_adm_dynamic_slot_default', function() {
	return '{key}/{site}/{newtag}_{zone}';
} );

PMC_Ad_Dynamic_Zone::get_instance()->register( 'newtag', function() {
	// to something & return a value for {newtag}
	return 'new-tag-value';
} );

*/

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Ad_Dynamic_Zone {

	use Singleton;

	protected $_callbacks = array();

	protected function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
		$this->register('pagezone', array( 'PMC','get_pagezone') );
		$this->register('term', array( $this,'get_term') );
		$this->register('vertical', array( $this,'get_vertical') );
		$this->register('category', array( $this,'get_category') );
		$this->register('loginstatus', array( $this,'get_loginstatus') );
	}

	public function action_init() {
		// we want this filter to run first before theme override
		add_filter( 'pmc_adm_google_publisher_slot', array( $this, 'filter_pmc_adm_google_publisher_slot' ), 9, 2 );
		add_filter( 'pmc_ad_provider_fields', array( $this, 'filter_pmc_ad_provider_fields' ), 10, 2 );
	}

	/**
	 * Filter to set the drop down options for dynamic slots selection
	 */
	public function filter_pmc_ad_provider_fields( $fields, $provider ) {
		if ( isset( $fields['dynamic_slot'] ) ) {
			$fields['dynamic_slot']['options'] = $this->get_dynamic_slots();
			$fields['dynamic_slot']['default'] = $this->get_dynamic_slot_default();
		}
		return $fields;
	}

	/**
	 * Filter to adjust the ad slot if dynamic slot is defined
	 */
	public function filter_pmc_adm_google_publisher_slot( $slot, $ad ) {
		if ( !empty( $ad['dynamic_slot'] ) ) {
			$slot = $ad['dynamic_slot'];

			// default required variables
			$variables = array( 'key' => 'key', 'site' => 'sitename', 'zone' => 'zone' );
			$variables = ( ! empty( $ad['provider'] ) && 'boomerang' === $ad['provider'] ) ? [ 'zone' => 'zone' ] : $variables;

			foreach ( $variables as $key => $value ) {
				$slot = str_replace( "{{$key}}", !empty( $ad[$value] ) ? $ad[$value] : '', $slot );
			}

			// process additional variable via registered callback functions
			foreach ( $this->_callbacks as $key => $callback ) {

				// check to make sure the dynamic slot contains variable for replacement
				if ( strpos( $slot, "{{$key}}" ) === false ) {
					continue;
				}

				$value = '';
				// make sure the callback is valid before calling the function
				if ( is_callable( $callback ) ) {
					$value = call_user_func( $callback, $ad );
				}

				// do variable replacement
				$slot = str_replace( "{{$key}}", $value, $slot );

			}


			// Add support for {uri} & {uri-part-x}
			$uri = ltrim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH) , '/' );
			$slot = str_replace( '{uri}', $uri, $slot );
			$slot = str_replace( '{uri-part}', '{uri-part-1}', $slot );

			$uri_parts = explode( '/', $uri );
			$i = 0;

			foreach ( $uri_parts as $value ) {
				$i++;
				$slot = str_replace( "{uri-part-{$i}}", $value, $slot );
			}

			// remove invalid variables
			$slot = preg_replace( '/\{.*?\}/','', $slot );
			// remove extra / & _ due to empty variables
			$slot = preg_replace( '/__+/','_', $slot );
			$slot = preg_replace( '@//@','/', $slot );
			$slot = preg_replace( '@_/|/_@','/', $slot );
			// trim invalid characters
			$slot = trim( $slot, " \t\n\r\0\x0B_-/" );
		}

		return $slot;
	}

	/**
	 * Register a new tag variable for dynamic slot replacement: {tagname}
	 * @param string $tagname The tagname of the variable
	 * @param function $callback The call back function that return a string for variable replacement
	 * @return $this
	 */
	public function register( $tagname, $callback ) {
		if ( !empty( $tagname ) && is_callable( $callback )) {
			$this->_callbacks[ $tagname ] = $callback;
		}
		return $this;
	}

	/**
	 * Return the term slug if queried object is taxonomy
	 * Use for callback function to replace {term} variable
	 * @return string
	 */
	public function get_term() {
		$queried_object = get_queried_object();
		if ( !empty( $queried_object ) && !empty( $queried_object->taxonomy )) {
			return $queried_object->slug;
		}
		return '';
	}

	/**
	 * Return category slug
	 * Use for callback function to replace {category} variable
	 * @return string
	 */
	public function get_category() {
		$queried_object = get_queried_object();
		if ( ! empty( $queried_object ) && ! empty( $queried_object->taxonomy ) && 'category' === $queried_object->taxonomy ) {
			//Get root category.

			//Array of ancestors from lowest to highest in the hierarchy
			$ancestors = get_ancestors( $queried_object->term_id, 'category' );

			if ( ! empty( $ancestors ) && is_array( $ancestors ) ) {
				$top_category_id = end( $ancestors );
				$top_category    = get_category( $top_category_id );
				if ( ! is_wp_error( $top_category ) & ! empty( ! empty( $top_category ) ) ) {
					return $top_category->slug;
				}
			}

			return $queried_object->slug;
		}

		if ( $post = get_post() ) {
			if ( class_exists( 'PMC_Primary_Taxonomy' ) ) {
				$term = PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $post, 'category' );
			} else {
				$terms = get_the_category();
				if ( !empty( $terms ) ) {
					$term = reset ( $terms );
				}
				unset( $terms );
			}
			if ( !empty( $term ) ) {
				return $term->slug;
			}
		}

		return '';
	}

	/**
	 * Return vertical slug
	 * Use for callback function to replace {vertical} variable
	 * @return string
	 */
	public function get_vertical() {
		if ( taxonomy_exists( 'vertical' ) ) {
			if ( $post = get_post() ) {
				if ( class_exists( 'PMC_Vertical' ) ) {
					$term = PMC_Vertical::get_instance()->primary_vertical( $post );
				} else {
					$terms = get_the_terms( $post, 'vertical' );
					if ( !empty( $terms ) ) {
						$term = reset( $terms );
					}
					unset( $terms );
				}
				if ( !empty( $term ) ) {
					return $term->slug;
				}
			} else {
				$queried_object = get_queried_object();
				if ( !empty( $queried_object ) && !empty( $queried_object->taxonomy ) && 'vertical' == $queried_object->taxonomy ) {
					return $queried_object->slug;
				}
			}
		}
		return '';
	}

	/**
	 * Return the login status if them support login status by checking pmc_login_status filter.
	 * @return string The login status value: li | lo | empty string
	 */
	public function get_loginstatus() {
		$status = apply_filters('pmc_login_status', '' );
		if ( is_bool( $status ) ) {
			$status = $status ? 'li' : 'lo';
		}
		return $status;
	}

	/**
	 * @return array The list of dynamic slot templates for variable replacement
	 */
	public function get_dynamic_slots() {
		$slots = array(
			'{key}/{site}/{zone}'            => '{key}/{site}/{zone}',
			'{key}/{site}/{zone}/{vertical}' => '{key}/{site}/{zone}/{vertical}',
			'{key}/{site}/{vertical}'        => '{key}/{site}/{vertical}',
			'{key}/{site}/{vertical}/{zone}' => '{key}/{site}/{vertical}/{zone}',
			'{key}/{site}/{uri-part}'        => '{key}/{site}/{uri-part}',
			'{key}/{site}/{zone}/{uri-part}' => '{key}/{site}/{zone}/{uri-part}',
			'{key}/{site}/{zone}/{category}' => '{key}/{site}/{zone}/{category}',
			'{key}/{site}/{category}'        => '{key}/{site}/{category}',
			'{key}/{site}/{category}/{zone}' => '{key}/{site}/{category}/{zone}',
		);
		return apply_filters( 'pmc_adm_dynamic_slots', $slots );
	}

	/**
	 * @return string The default dynamic slot template
	 */
	public function get_dynamic_slot_default() {
		return apply_filters( 'pmc_adm_dynamic_slot_default', '{key}/{site}/{zone}' );
	}

}

PMC_Ad_Dynamic_Zone::get_instance();
