<?php

namespace PMC\Core\Plugins\Filter_Posts;

use PMC\Global_Functions\Traits\Singleton;

class Filters {
	use Singleton;

	protected $_conditions = [
			'is_home',
		];

	protected $_post_types = [
			'post',
		];

	/**
	 * Class constructor to initialize the class object
	 */
	protected function __construct() {
		add_action( 'init', [ $this, 'action_init' ] );
	}

	/**
	 * The init wp action
	 */
	public function action_init() {
		if ( ! is_admin() ) {
			// We want to check condition first before we add pre_get_posts action
			// To avoid constant condition inside preg_get_posts action hook for each get post query
			add_action( 'parse_query', [ $this, 'action_parse_query' ] );
		}
	}

	/**
	 * Action to setup pre_get_posts hook
	 */
	public function action_parse_query() {
		if ( $this->_is_active() ) {
			add_action( 'pre_get_posts', [ $this, 'do_filter_posts'] );
		}
	}

	/**
	 * Action to do post type filter
	 * @param  WP_Query $query The WP Query
	 */
	public function do_filter_posts( $query ) {
		if ( ! $query->is_main_query() || empty( $this->_post_types ) ) {
			return;
		}
		$query->set( 'post_type', $this->_post_types );
	}

	/**
	 * Add a post type to include in the query
	 * @param  string $post_type The post type to include
	 */
	public function register_post_type( $post_type ) {
		$post_type = trim( $post_type );
		if ( ! $post_type || in_array( $post_type, $this->_post_types, true ) ) {
			return;
		}
		$this->_post_types[] = $post_type;
	}

	/**
	 * Add a condition function to check when to do the post type filters
	 * @param  function $function The callable function
	 */
	public function register_condition( $function ) {
		$function = trim( $function );
		if ( ! $function || in_array( $function, $this->_conditions, true ) || ! is_callable( $function ) ) {
			return;
		}
		$this->_conditions[] = $function;
	}

	/**
	 * Return true if one of the condition is true
	 * @return boolean
	 */
	protected function _is_active() {
		// This condition should never be true, but just in case, we check anyway
		if ( empty( $this->_post_types ) || empty( $this->_conditions ) ) {
			return false;
		}
		foreach( $this->_conditions as $func ) {
			if ( ! is_callable( $func ) ) {
				continue;
			}
			if ( call_user_func( $func ) ) {
				return true;
			}
		}
		return false;
	}

}
