<?php
/**
 * PMC Core Menu setup.
 *
 * @package pmc-core-v2
 *
 * @since   2018-01-17
 */

namespace PMC\Core\Inc;

use \PMC\Global_Functions\Traits\Singleton;

class Menu {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * Initializes the theme.
	 */
	protected function __construct() {

		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() {

		add_action( 'wp_update_nav_menu_item', [ $this, 'clear_menu_cache' ] );
	}

	/**
	 * Get Menu Items for a menu Location
	 *
	 * @param string $location Menu location.
	 *
	 * @return array
	 */
	public function get_menu_data( string $location ) {

		if ( empty( $location ) ) {
			return [];
		}

		$menu_cache_data = wp_cache_get( $location, 'pmc_core_menu' );

		if ( ! empty( $menu_cache_data ) ) {
			return $menu_cache_data;
		}

		// Get all locations.
		$menu_locations = get_nav_menu_locations();

		if ( empty( $menu_locations[ $location ] ) ) {
			return [];
		}

		// Get menu id by location.
		$menu = wp_get_nav_menu_object( $menu_locations[ $location ] );

		if ( ! ( $menu instanceof \WP_Term ) ) {
			return [];
		}

		// Get menu items by menu name.
		$menu_items = wp_get_nav_menu_items( $menu->term_id );

		if ( empty( $menu_items ) ) {
			return [];
		}

		$menu_data        = [];
		$menu_data_childs = [];

		foreach ( (array) $menu_items as $menu_item ) {
			$data                       = [];
			$data['id']                 = $menu_item->ID;
			$data['c_nav_link_text']    = $menu_item->title;
			$data['slug']               = \sanitize_title_with_dashes( $menu_item->title );
			$data['c_nav_link_url']     = $menu_item->url;
			$data['menu_item_parent']   = $menu_item->menu_item_parent;
			$data['type']               = $menu_item->type;
			$data['c_nav_link_classes'] = implode( ' ', $menu_item->classes );

			if ( ! empty( $menu_item->menu_item_parent ) ) {
				$menu_data[ $menu_item->menu_item_parent ]['child'][] = $data;
				$menu_data_childs[ $menu_item->menu_item_parent ]     = $menu_data[ $menu_item->menu_item_parent ];
			} else {
				$menu_data[ $menu_item->ID ] = $data;
			}
		}

		$menu_cache_data = [
			'root'  => $menu_data,
			'child' => $menu_data_childs,
		];

		wp_cache_add( $location, $menu_cache_data, 'pmc_core_menu' );

		return $menu_cache_data;

	}

	/**
	 * Delete menu cache that we created in get_menu_data, after menu updates
	 */
	public function clear_menu_cache() {

		$menu_locations = get_nav_menu_locations();

		foreach ( $menu_locations as $location => $id ) {
			wp_cache_delete( $location, 'pmc_core_menu' );
		}

	}

}

//EOF
