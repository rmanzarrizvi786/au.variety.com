<?php
/**
 * Variety Comisioned Articles plugin class.
 *
 * @author Adaeze Esiobu
 *
 * @since   2014-10-07
 * @version 2017-09-12 - Dhaval Parekh - CDWE-627 - Copied from pmc-variety-2014
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Exclusive_Articles;

use \PMC\Global_Functions\Traits\Singleton;

class Plugin {

	use Singleton;

	/**
	 * Post type slug.
	 *
	 * @var string Post type slug.
	 */
	const POST_TYPE = 'exclusive';

	const POST_SLUG_PREFIX = 'exclusive';

	const ARCHIVE_TITLE = 'Exclusive';

	protected $_default_category = -1;

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
		 * Actions
		 */
		add_action( 'init', array( $this, 'on_action_init' ) );

		/**
		 * Filters
		 */
		add_filter( 'variety-vertical-post-types', array( $this, 'add_to_vertical_taxonomy' ) );
		add_filter( 'post_type_archive_title', array( $this, 'get_archive_title' ), 10, 2 );
	}

	/**
	 * To call functions on init action.
	 *
	 * @hook   init
	 *
	 * @return void
	 */
	public function on_action_init() {

		$this->_register_post_type();

		$this->_setup_url_rewrite();
	}

	/**
	 * Register the Exclusive custom post type.
	 *
	 * @return void
	 */
	protected function _register_post_type() {

		$labels = array(
			'name'               => _x( 'Exclusive Content', 'Post Type General Name', 'pmc-variety' ),
			'singular_name'      => _x( 'Exclusive Content', 'Post Type Singular Name', 'pmc-variety' ),
			'menu_name'          => __( 'Exclusive Content', 'pmc-variety' ),
			'all_items'          => __( 'All Exclusive Posts', 'pmc-variety' ),
			'add_new'            => __( 'Add New', 'pmc-variety' ),
			'add_new_item'       => __( 'Add New Exclusive Post', 'pmc-variety' ),
			'edit_item'          => __( 'Edit Post', 'pmc-variety' ),
			'view_item'          => __( 'View Post', 'pmc-variety' ),
			'search_items'       => __( 'Search Post', 'pmc-variety' ),
			'not_found'          => __( 'Not found', 'pmc-variety' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'pmc-variety' ),
			'parent_item_colon'  => __( 'Parent Post:', 'pmc-variety' ),
		);

		$args = array(
			'description'         => __( 'Exclusive content for Variety', 'pmc-variety' ),
			'labels'              => $labels,
			'public'              => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_nav_menus'   => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => array(
				'title', 'editor', 'author', 'thumbnail',
				'excerpt', 'custom-fields',
				'comments', 'revisions',
			),
			'taxonomies'          => array( 'category', 'post_tag' ),
			'has_archive'         => true,
			'rewrite'             => true,
			'query_var'           => self::POST_SLUG_PREFIX,
			'can_export'          => true,
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Set up rewrite rule for feed.
	 *
	 * @return void
	 */
	protected function _setup_url_rewrite() {

		// Rewrite rule for feed.
		add_rewrite_rule(
			'^' . self::POST_SLUG_PREFIX . '/feed/?',
			'index.php?feed=rss2&post_type=' . self::POST_TYPE,
			'top'
		);

	}

	/**
	 * Utility function to grab the default category from DB, store it in class var for
	 * the duration of execution cycle and return it, kind of like a volatile cache.
	 * Since this class is Singleton, this feature is part of the deal.
	 *
	 * @return string Slug of the default category or empty string if there is no default category
	 */
	public function get_default_category() {

		if ( -1 === $this->_default_category ) {

			$default_category = get_category( get_option( 'default_category' ) );
			$this->_default_category = ( empty( $default_category ) || is_wp_error( $default_category ) ) ? '' : $default_category->slug;

		}

		return $this->_default_category;
	}

	/**
	 * This function adds our custom post type to 'vertical' taxonomy to allow
	 * usage of verticals in this post type.
	 *
	 * @param  array $post_types Array of post types on which 'vertical' taxonomy should be enabled.
	 *
	 * @return array Array of post types on which 'vertical' taxonomy should be enabled
	 */
	public function add_to_vertical_taxonomy( $post_types = array() ) {

		if ( is_array( $post_types ) ) {
			$post_types[] = self::POST_TYPE;
		}

		return $post_types;
	}

	/**
	 * Hooked on 'post_type_archive_title' filter, this function sets a custom
	 * title for archive pages of this post type
	 *
	 * @param  string $title Default title which will be displayed.
	 * @param  string $post_type Post type for which title is to be displayed.
	 *
	 * @return string $title Title which will be displayed
	 */
	public function get_archive_title( $title, $post_type ) {

		if ( self::POST_TYPE === $post_type ) {
			$title = self::ARCHIVE_TITLE;
		}

		return $title;
	}

}

