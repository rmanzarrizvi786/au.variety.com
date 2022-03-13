<?php

/*
Plugin Name: PMC Top Videos
Plugin URI: http://www.pmc.com
Version: 1.0
Author: Amit Sannad, PMC
Author URI: http://www.pmc.com
License: PMC Proprietary. All rights reserved.

This plugin adds a new menu to the WP admin dashboard, which allows to create new post types for adding videos.
*/

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Top_Videos {

	use Singleton;

	const post_type_name = 'pmc_top_video';

	/**
	 * Initialize the plugin and add actions for Varierty Top Videos
	 *
	 * @param void
	 *
	 * @return void.
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'custom_metadata_manager_init_metadata', array( $this, 'init_custom_fields' ) );
		add_action( 'after_setup_theme', array( $this, 'setup' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_query' ) );
		add_filter( 'pmc_sitemaps_post_type_whitelist', [ $this, 'whitelist_post_type_for_sitemaps' ] );
	}


	public function setup() {
		add_image_size( 'pmc-top-videos-widget', 236, 168, true ); // primarily used on author
	}

	/**
	 * Registers different post types and taxonomies under the plugin.
	 *
	 * @param void
	 *
	 * @return void.
	 */
	public function register_post_type() {
		register_post_type(
			self::post_type_name, array(
				'labels'      => array(
					'name'               => __( 'Videos', 'pmc-plugins' ),
					'singular_name'      => __( 'Video', 'pmc-plugins' ),
					'add_new'            => _x( 'Add New', 'Video', 'pmc-plugins' ),
					'add_new_item'       => __( 'Add New Video', 'pmc-plugins' ),
					'edit_item'          => __( 'Edit Video', 'pmc-plugins' ),
					'new_item'           => __( 'New Video', 'pmc-plugins' ),
					'view_item'          => __( 'View Video', 'pmc-plugins' ),
					'search_items'       => __( 'Search Videos', 'pmc-plugins' ),
					'not_found'          => __( 'No Videos found.', 'pmc-plugins' ),
					'not_found_in_trash' => __( 'No Videos found in Trash.', 'pmc-plugins' ),
					'all_items'          => __( 'Videos', 'pmc-plugins' )
				),
				'public'      => true,
				'supports'    => array( 'title', 'author', 'comments', 'editor', 'thumbnail' ),
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'video' ),
				'taxonomies'  => array( 'category', 'post_tag', 'vertical', 'vcategory' )
			)
		);

		register_taxonomy(
			'vcategory', self::post_type_name, array(
				'labels'            => array(
					'name'          => _x( 'Playlists', 'taxonomy general name', 'pmc-plugins' ),
					'singular_name' => _x( 'Playlist', 'taxonomy singular name', 'pmc-plugins' ),
				),
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_in_nav_menus' => false,
				'show_admin_column' => true
			)
		);
	}

	/**
	 * Makes sure the correct content displays on the video page.
	 *
	 * Sets the video page to mirror the archive page for the pmc_top_video
	 * CPT, but due to nav requirements it needs to be this vertical archive. This
	 * also allows for the filtering of videos based on $_GET parameters
	 *
	 * @param obj $wp_query The current WP_Query object
	 *
	 * @return obj The filtered WP_Query object
	 */
	public function filter_query( $wp_query ) {
		$video_vertical = apply_filters( 'pmc-top-videos-vertical', '' );

		if ( empty( $video_vertical ) || ! PMC::is_vertical( $video_vertical ) || ! $wp_query->is_main_query() ) {
			return;
		}

		//pretend we are actually the pmc_top_video archive
		$wp_query->set( 'vertical', '' );
		$wp_query->set( 'tax_query', '' );
		$wp_query->set( 'post_type', 'pmc_top_video' );
		$wp_query->set( 'posts_per_page', 12 );

	}

	/**
	 * Add Voices field for variety.
	 */
	public function init_custom_fields() {

		if ( function_exists( 'x_add_metadata_field' ) && function_exists( 'x_add_metadata_group' ) ) {

			$grp_args = array(
				'label' => 'Video Data'
			);

			x_add_metadata_group( '_pmc_top_video', self::post_type_name, $grp_args );

			$args = array(
				'group'      => '_pmc_top_video',
				'field_type' => 'wysiwyg',
				'label'      => 'Description',
			);

			x_add_metadata_field( '_pmc_top_video_caption', self::post_type_name, $args );

			$args = array(
				'group'      => '_pmc_top_video',
				'field_type' => 'text',
				'label'      => 'Dek',
			);

			x_add_metadata_field( '_pmc_top_video_dek', self::post_type_name, $args );

		}
	}


	public function get_meta_data( $post_id = "", $key = "" ) {
		if ( empty( $post_id ) || empty( $key ) ) {
			return;
		}


		return get_post_meta( $post_id, "_pmc_top_video_" . $key, true );
	}

	/**
	 * Get all the posts associated with the plugin
	 *
	 * @param int $count . Number of posts to be fetched from the db
	 *
	 * @return array $posts. Returns an array of posts fetched from the db. It will be a double dimension *                array in an resultset format.
	 */
	function fetch_recent_slider_posts( $count = 3 ) {

		$orig_count = $count;

		//Dont fetch more then 100
		$count = min( $count, 100 );

		//Get extra post to exclude current post
		if ( is_single() ) {
			$count = $count + 1;
		}

		$video_args = array(
			'posts_per_page' => $count,
			'post_type'      => self::post_type_name,
			'post_status'    => 'publish',

		);

		$terms = get_the_terms( get_the_ID(), 'vcategory' );

		if ( ! empty( $terms ) ) {

			$slug = wp_list_pluck( $terms, "slug" );

			$video_args['tax_query'] = array(
				array(
					'taxonomy' => 'vcategory',
					'field'    => 'slug',
					'terms'    => array_values( $slug ),
				)
			);
		}

		$posts = get_posts( $video_args );

		$return_array = array();

		$i = 0;
		foreach ( $posts as $v_post ) {
			if ( $orig_count == $i ) {
				break;
			}
			if ( is_single() && get_the_ID() == $v_post->ID ) {
				continue;
			}
			$return_array[$v_post->ID] = $v_post;
			$i ++;
		}

		return $return_array;
	}

	public static function get_available_channels() {

		$vcategories = get_terms( 'vcategory' );

		if ( ! empty( $vcategories ) ) {
			$slug = wp_list_pluck( $vcategories, "slug" );

			return array_values( $slug );
		}
	}

	/**
	 * Whitelist post type for sitemap.
	 *
	 * @param  array $post_types List of post type for site map.
	 *
	 * @return array List of post type for site map.
	 */
	public function whitelist_post_type_for_sitemaps( $post_types ) {

		$post_types = ( ! empty( $post_types ) && is_array( $post_types ) ) ? $post_types : [];

		if ( ! in_array( self::post_type_name, (array) $post_types, true ) ) {
			$post_types[] = self::post_type_name;
		}

		return $post_types;
	}

}

//EOF
