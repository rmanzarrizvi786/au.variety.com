<?php
namespace PMC\Touts;

use PMC\Global_Functions\Traits\Singleton;

class Tout {
	use Singleton;

	// Post type name
	const POST_TYPE_NAME = 'tout';

	protected function __construct() {
		// Using late action since this object is instantiated from Plugin init priority 15
		add_action( 'init', [ $this, 'create_post_type' ], 20 );
		add_filter( 'pre_post_link', array( $this, 'post_type_link' ), 10, 2 );
		add_filter( 'post_type_link', array( $this, 'post_type_link' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'remove_touts_from_frontend' ), 15 );
	}

	/**
	 * Register the post type
	 */
	public function create_post_type() {

		register_post_type(
			self::POST_TYPE_NAME,
			array(
				'labels'              => array(
					'name'               => __( 'Touts', 'pmc-touts' ),
					'singular_name'      => __( 'Tout', 'pmc-touts' ),
					'add_new'            => __( 'Add New Tout', 'pmc-touts' ),
					'add_new_item'       => __( 'Add New Tout', 'pmc-touts' ),
					'edit_item'          => __( 'Edit Tout', 'pmc-touts' ),
					'new_item'           => __( 'New Tout', 'pmc-touts' ),
					'view_item'          => __( 'View Tout', 'pmc-touts' ),
					'search_items'       => __( 'Search Touts', 'pmc-touts' ),
					'not_found'          => __( 'No Touts found', 'pmc-touts' ),
					'not_found_in_trash' => __( 'No Touts found in Trash', 'pmc-touts' ),
					'parent_item_colon'  => __( 'Parent Tout:', 'pmc-touts' ),
					'menu_name'          => __( 'Touts', 'pmc-touts' ),
				),
				'public'              => true,
				'exclude_from_search' => true,
				'show_in_nav_menus'   => true,
				'publicly_queryable'  => false,
				'rewrite'             => false,
				'supports'            => array( 'title', 'thumbnail', 'excerpt', 'zoninator_zones' ),
				'taxonomies'          => array( 'category', 'vertical', 'editorial' ),
				'menu_icon'           => 'dashicons-images-alt',
				'menu_position'       => '5.9',
			)
		);
	}

	public function post_type_link( $link, $post ) {
		if ( self::POST_TYPE_NAME === $post->post_type ) {
			if ( '' !== ( $url = esc_url_raw( get_post_meta( $post->ID, 'tout_link', true ) ) ) ) {
				return $url;
			} else {
				// All touts should have valid URLs. If the tout does not, we
				// will just return a hash mark.
				return '#';
			}
		}

		return $link;
	}

	/**
	 *
	 * @param \WP_Query $query
	 *
	 * @return void
	 */
	public function remove_touts_from_frontend( $query ) {

		if ( ! is_object( $query ) || is_admin() || is_feed() ) {
			return;
		}

		$post_types = $query->get( 'post_type' );

		if ( empty( $post_types ) || ! is_array( $post_types ) ) {
			return;
		}

		$index = array_search( self::POST_TYPE_NAME, $post_types );

		if ( false !== $index ) {
			unset( $post_types[ $index ] );
			$query->set( 'post_type', array_filter( $post_types ) );
		}

	}

	/**
	 * Set has_archive => true for this post type. We do this outside of the
	 * registration call if we want to handle the rewrites ourselves.
	 */
	public function enable_archives() {
		global $wp_post_types;
		if ( ! empty( $wp_post_types[ self::POST_TYPE_NAME ] ) ) {
			$wp_post_types[ self::POST_TYPE_NAME ]->has_archive = true;
		}
	}

}
