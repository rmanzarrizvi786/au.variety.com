<?php
/**
 * Config file for pmc-content plugin from pmc-plugins
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2017-09-12
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Content\Admin as PMC_Content_Admin;

class PMC_Content {

	use Singleton;

	/**
	 * Construct Method.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * To setup actions/filters.
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/**
		 * Filters
		 */
		add_filter( 'pmc_feature_video_post_types', array( $this, 'add_post_types' ) );
		add_filter( 'pmc-vertical-post-types', array( $this, 'add_post_types' ) );

	}

	/**
	 * To add metabox in pmc-content add/edit screen.
	 *
	 * @hook   pmc_feature_video_post_types
	 * @hook   pmc-vertical-post-types
	 *
	 * @param  array $post_types List of post types.
	 *
	 * @return array List of post types.
	 */
	public function add_post_types( $post_types ) {

		if ( empty( $post_types ) || ! is_array( $post_types ) ) {
			$post_types = array();
		}

		$post_types[] = PMC_Content_Admin::NAME;

		return $post_types;
	}

}
