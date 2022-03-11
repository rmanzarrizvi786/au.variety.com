<?php
/**
 * Config file for pmc-vertical plugin from pmc-plugins
 *
 * @author  Chandra Patel <chandrakumar.patel@rtcamp.com>
 *
 * @since   2017-10-05
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC_Vertical as PMC_Vertical_Plugin;

class PMC_Vertical {

	use Singleton;

	/**
	 * Plugin Instance
	 *
	 * @var \PMC_Vertical
	 */
	protected $_plugin_instance;

	/**
	 * Setup various hooks
	 */
	protected function __construct() {

		$this->_plugin_instance = PMC_Vertical_Plugin::get_instance();

		add_filter( 'post_link', array( $this, 'remove_plugin_post_link_filters' ), 1, 2 );
		add_filter( 'post_type_link', array( $this, 'remove_plugin_post_type_link_filters' ), 1, 2 );

		add_filter( 'pmc-vertical-post-types', [ $this, 'filter_pmc_vertical_post_types' ] );

	}

	/**
	 * Filter post types with which the 'vertical' taxonomy will associate
	 *
	 * @param  array $post_types List of post types.
	 *
	 * @return array List of post types
	 */
	public function filter_pmc_vertical_post_types( $post_types ) {

		if ( empty( $post_types ) || ! is_array( $post_types ) ) {
			$post_types = array();
		}

		$allowed_post_types = [ 'pmc-gallery', 'pmc_list', 'variety_top_video', 'exclusive', 'tout' ];

		$post_types = array_merge( $post_types, $allowed_post_types );

		return $post_types;

	}

	/**
	 * Remove extra plugin filter for tout post type.
	 * which manipulate URL.
	 *
	 * @param string   $link Post Url.
	 * @param \WP_Post $post Post Object.
	 *
	 * @return string
	 */
	public function remove_plugin_post_link_filters( $link, $post = 0 ) {

		if ( 'tout' === get_post_type( $post ) ) {
			remove_filter( 'post_link', array( $this->_plugin_instance, 'filter_permalink_tags' ), 10 );
		}

		return $link;
	}

	/**
	 * Remove extra plugin filter for tout post type.
	 * which manipulate URL.
	 *
	 * @param string   $link Post Url.
	 * @param \WP_Post $post Post Object.
	 *
	 * @return string
	 */
	public function remove_plugin_post_type_link_filters( $link, $post = 0 ) {

		if ( 'tout' === get_post_type( $post ) ) {
			remove_filter( 'post_type_link', array( $this->_plugin_instance, 'filter_permalink_tags' ), 10 );
		}

		return $link;
	}

}

//EOF
