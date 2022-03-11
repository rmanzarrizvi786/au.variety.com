<?php
/**
 * Config class for zoninator plugin
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2017-08-29 - CDWE-614
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class Zoninator {

	use Singleton;

	/**
	 * Construct function for current class.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 *
	 * To setup actions/filters.
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/**
		 * Actions
		 */
		add_action( 'zoninator_pre_init', [ $this, 'add_post_types' ] );

		/**
		 * Filters
		 */
		// Bind late, because pmc-core is adding thumbnail column at default priority.
		add_filter( 'zoninator_zone_post_columns', [ $this, 'zone_post_columns' ], 11 );

		add_filter( 'zoninator_search_args', [ $this, 'search_args' ] );

	}

	/**
	 * To manage column of post in admin zone page.
	 *
	 * @hook   zoninator_zone_post_columns
	 *
	 * @param  array $columns List of column to be show in admin zone page.
	 *
	 * @return array
	 */
	public function zone_post_columns( $columns ) {

		if ( empty( $columns ) || ! is_array( $columns ) ) {
			return $columns;
		}

		unset( $columns['thumbnail'] );

		return $columns;
	}

	/**
	 * To add post type in zoninator.
	 *
	 * @hook   zoninator_pre_init
	 *
	 * @return void
	 */
	public function add_post_types() {

		$available_post_types = [
			'tout',
			'hollywood_exec',
			'pmc_list',
			'pmc-gallery',
			'variety_top_video',
			'variety_vip_post',
			'variety_vip_report',
			'variety_vip_video',
		];

		foreach ( $available_post_types as $post_type ) {
			if ( post_type_exists( $post_type ) ) {
				add_post_type_support( $post_type, 'zoninator_zones' );
			}
		}

	}

	/**
	 * @codeCoverageIgnore this is for 911 fix, lets ignore test
	 * Offload Zones search to elastic search
	 *
	 * @param $post_args
	 *
	 * @return mixed
	 */
	public function search_args( $post_args ) {

		$post_args['date_query'] = [
			'after' => '180 day ago',
		];

		return $post_args;
	}

}
