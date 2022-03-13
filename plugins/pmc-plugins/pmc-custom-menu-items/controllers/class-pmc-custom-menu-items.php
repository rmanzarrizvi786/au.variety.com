<?php

/**
 * PMC Custom Menu Items base class
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since 2014-08-18
 */

use \PMC\Global_Functions\Traits\Singleton;

abstract class PMC_Custom_Menu_Items {

	use Singleton;

	/**
	 * Custom post type name
	 */
	const POST_TYPE = 'pmc-custom-menu-item';

	/**
	 * Plugin ID used to prefix unique identifiers
	 */
	const PLUGIN_ID = 'pmc-cmi';

	/**
	 * Filter hook prefix for whitelisting and preventing any irrelevant (or dangerous WP) filter from being set
	 */
	const FILTER_PREFIX = 'pmc-custom-menu-item-';


	/**
	 * Initialization
	 *
	 * @return void
	 */
	protected function __construct() {
		$this->_setup_hooks();
		$this->_setup_child_hooks();
	}


	/**
	 * Setup hooks which are mandatory for all children
	 *
	 * @return void
	 */
	private function _setup_hooks() {
		/**
		 * actions
		 */
		add_action( 'init', array( $this, 'register_post_type' ) );
	}


	/**
	 * abstract method for all child classes to implement to setup their
	 * respective hooks
	 */
	abstract protected function _setup_child_hooks();


	/**
	 * This function registers the custom post type
	 *
	 * @return void
	 */
	final public function register_post_type() {
		register_post_type( self::POST_TYPE, array(
			'labels'              => array(
				'name'          => 'Custom Menu Items',
				'singular_name' => 'Custom Menu Item',
				'add_new_item'  => 'Add New Custom Menu Item',
				'edit_item'     => 'Edit Custom Menu Item',
			),
			'description'         => 'A custom post type to store custom menu items usable in nav menus anywhere on the site',
			'public'              => false,					//private post type
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => true,					//enable wp-admin UI
			'show_in_nav_menus'   => true,		//make it selectable for use in nav menus
			'show_in_menu'        => 'themes.php',		//show it under "Appearance" menu
			'show_in_admin_bar'   => false,		//no need to put in admin bar
			'hierarchical'        => false,
			'supports'            => array( 'title' ),
			'rewrite'             => false,					//url rewrites not needed for this
			'can_export'          => true,				//allow post export
		) );
	}


	/**
	 * This function returns the callback data for a custom menu item as an array
	 *
	 * @param WP_Post/int $item A post object or post ID of a custom menu item
	 * @return array/bool Returns an array containing callback details for the custom menu item or FALSE if menu item does not exist
	 */
	protected function _get_callback_data( $item ) {
		if ( empty( $item ) ) {
			return false;
		}

		if ( is_object( $item ) && ! empty( $item->post_content ) ) {
			$data = $item->post_content;
		}

		if ( is_numeric( $item ) && intval( $item ) > 0 ) {
			$data = get_post_field( 'post_content', intval( $item ), 'raw' );

			if ( is_wp_error( $data ) ) {
				$data = '';
			}
		}

		if ( ! empty( $data ) ) {
			$data = json_decode( $data, true );

			if ( is_array( $data ) ) {
				return $data;
			}
		}

		return false;
	}

}	//end of class


//EOF
