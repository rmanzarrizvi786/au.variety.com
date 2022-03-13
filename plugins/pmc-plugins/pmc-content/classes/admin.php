<?php

namespace PMC\Content;

use \PMC;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class for registering the general Content post type
 *
 * @since 2015-07-07
 * @version 2015-07-07 Mike Auteri - PPT-5070 - Create this new general post type
 */

class Admin {

	use Singleton;

	const NAME = 'pmc-content';

	/**
	 * Constructor function
	 *
	 * @since 2015-07-07
	 * @version 2015-07-07 Mike Auteri - PPT-5070 - Create this new general post type
	 * @return void
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'pmc_page_meta_expose_authors', array( $this, 'expose_author_post_types' ) );
		add_filter( 'pmc_gallery_link_post_types', array( $this, 'pmc_gallery_link_post_types') );
	}

	/**
	 * Register Content post type
	 *
	 * @since 2015-07-07
	 * @version 2015-07-07 Mike Auteri - PPT-5070 - Create this new general post type
	 * @return void
	 */
	public function register_post_type() {
		register_post_type( self::NAME,
			array(
				'labels' => array(
					'name' => __( 'Content', 'pmc-plugins' ),
					'singular_name' => __( 'Content', 'pmc-plugins' ),
					'menu_name' => __( 'No Homepage', 'pmc-plugins' ),
					'name_admin_bar' => __( 'No Homepage', 'pmc-plugins' ),
					'add_new' => __( 'Add New Content', 'pmc-plugins' ),
					'add_new_item' => __( 'Add New Content', 'pmc-plugins' ),
					'edit' => __( 'Edit Content', 'pmc-plugins' ),
					'edit_item' => __( 'Edit Content', 'pmc-plugins' ),
					'new_item' => __( 'New Content', 'pmc-plugins' ),
					'view' => __( 'View Content', 'pmc-plugins' ),
					'view_item' => __( 'View Content', 'pmc-plugins' ),
					'search_items' => __( 'Search Content', 'pmc-plugins' ),
					'not_found' => __( 'No Content found', 'pmc-plugins' ),
					'not_found_in_trash' => __( 'No Content found in Trash', 'pmc-plugins' ),
				),
				'public' => true,
				'menu_position' => 6,
				'supports' => apply_filters( 'pmc_content_supports', array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail', 'custom-fields', 'trackbacks', 'revisions' ) ),
				'has_archive' => true,
				'rewrite' => array(
					'slug' => apply_filters( 'pmc_content_standalone_slug', 'content' )
				),
				'taxonomies' => apply_filters( 'pmc_content_taxonomies', array( 'category', 'post_tag' ) ),
			)
		);
	}

	/**
	 * Adds Content post type to PMC_Page_Meta filter for exposing authors
	 *
	 * @since  2015-07-09
	 * @version  2015-07-09 Mike Auteri - PPT-5070 - Create this new general post type
	 * @param $post_types
	 * @return array
	 */
	public function expose_author_post_types( $post_types = array() ) {
		$post_types[] = self::NAME;
		return $post_types;
	}

	/**
	 * @param array $post_types
	 * @return array
	 * Add Content post type to gallery link filter
	 */
	public function pmc_gallery_link_post_types( $post_types = array() ){
		$post_types[] = self::NAME;
		return $post_types;
	}

}

// EOF
