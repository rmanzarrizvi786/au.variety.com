<?php
namespace PMC\Core\Inc;

class Admin {

	use \PMC\Global_Functions\Traits\Singleton;

	protected function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		// This action has non-standard priority '8' as child-theme may also try to add sub-menu to menus
		// register here and they should be available by the time child tries to add.
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 8 );
		add_action( 'admin_menu', [ $this, 'admin_submenus' ], 20 );
		add_action( 'init', [ $this, 'simplify_nav_menus' ], 20 );
		add_action( 'wp_update_nav_menu', [ $this, 'clear_menu_transients' ], 100 );

		add_filter( 'admin_page_locking_screens', [ $this, 'admin_page_locking_screens' ] );
		add_filter( 'admin_page_locking_max_lock_period', [ $this, 'admin_curation_max_lock' ] );

		// Move Carousal menu under Curation menu.
		add_filter( 'register_post_type_args', [ $this, 'move_carousal_menu' ], 10, 2 );
		add_filter( 'parent_file', [ $this, 'maybe_highlight_carousal_taxonomy_menu' ] );

		/**
		 * Below hook must need to add on more than 10 priority, as Zones menu is added on 10 priority,
		 * so that menu needs to remove first and then add new Zones menu under curation,
		 * for achieving those two things it must hook on priority as 20.
		 */
		add_action( 'admin_menu', [ $this, 'move_carousal_taxonomy_menu' ], 20 );
	}

	public function admin_menu() {
		add_menu_page( __( 'Curation', 'pmc-core' ), __( 'Curation', 'pmc-core' ), 'edit_posts', 'curation', '__return_false', 'dashicons-randomize', '4.1' );
		add_menu_page( __( 'Taxonomies', 'pmc-core' ), __( 'Taxonomies', 'pmc-core' ), 'manage_categories', 'taxonomy', '__return_false', 'dashicons-category', '6.0' );
	}

	public function admin_submenus() {
		global $submenu, $zoninator;

		$remove_top_levels = [
			'curation',
			'taxonomy',
		];

		foreach ( $remove_top_levels as $slug ) {

			if ( isset( $submenu[ $slug ] ) && is_array( $submenu[ $slug ] ) ) {

				// Curation menu treat separately.
				if ( 'curation' === $slug ) {

					foreach ( $submenu[ $slug ] as $key => $val ) {

						if ( ! empty( $val[2] ) && 'curation' === $val[2] ) {

							unset( $submenu[ $slug ][ $key ] );

							break;
						}
					}
				} else {
					array_shift( $submenu[ $slug ] ); // @codingStandardsIgnoreLine: typecast here results in fatal, ignore till further notice.
				}
			}
		}

		// Move zoninator as a submenu item of Curation.
		if ( $zoninator ) {
			remove_menu_page( $zoninator->key );
			add_submenu_page( 'curation', __( 'Zoninator', 'zoninator' ), __( 'Zones', 'zoninator' ), $zoninator->_get_manage_zones_cap(), $zoninator->key, [ $zoninator, 'admin_page' ] );
		}
	}

	/**
	 * Remove some content types (that the theme didn't register) from the nav menu
	 * system to improve its performance.
	 */
	public function simplify_nav_menus() {
		global $wp_post_types;

		foreach ( [ 'guest-author', 'pmc-gallery', '_pmc-custom-feed' ] as $post_type ) {
			if ( ! empty( $wp_post_types[ $post_type ] ) ) {
				$wp_post_types[ $post_type ]->show_in_nav_menus = false;
			}
		}
	}

	/**
	 * Delete transients for stored menus when the menu is saved.
	 *
	 * @param  integer $menu_id
	 *
	 * @return void
	 */
	public function clear_menu_transients( $menu_id ) {
		$menu_locations = get_nav_menu_locations();

		if ( ! is_array( $menu_locations ) ) {
			return;
		}

		foreach ( $menu_locations as $location => $id ) {
			if ( $menu_id === $id ) {
				delete_transient( 'menu_object_' . $location );
				delete_transient( 'menu_object_items_' . $location );
				return;
			}
		}
	}

	public function admin_page_locking_screens( $screens ) {
		$screens[] = 'curation_page_global_curation';

		return $screens;
	}

	/**
	 * Uses cheezcap to set max time lock on global curation. 0 disables feature.
	 *
	 * @param $time
	 *
	 * @return int
	 */
	public function admin_curation_max_lock( $time ) {
		$time = \PMC_Cheezcap::get_instance()->get_option( 'curation-max-time-lock' );
		return intval( $time ) * 60;
	}

	/**
	 * An admin helper to get the current post's parent category.
	 * Use on the post edit screen.
	 *
	 * @return int Category ID or -1.
	 */
	public function get_current_parent_category() {

		$post_id = \PMC::filter_input( INPUT_GET, 'post', FILTER_VALIDATE_INT );

		if ( intval( $post_id ) < 1 ) {
			return -1;
		}

		$cat = get_post_meta( $post_id, 'categories', true );

		if ( is_numeric( $cat ) ) {
			return intval( $cat );
		}

		return -1;
	}

	/**
	 * Move the Carousal menu item to be a submenu item of "Curation".
	 *
	 * @param array  $args List of arguments for CPT.
	 * @param string $name Slug of CPT.
	 *
	 * @return array List of arguments for CPT.
	 */
	public function move_carousal_menu( $args, $name ) {

		$pmc_master_featured_articles = \PMC_Master_Featured_Articles::get_instance();

		if (
			! empty( $pmc_master_featured_articles->featured_post_type ) &&
			$pmc_master_featured_articles->featured_post_type === $name
		) {

			$args['show_in_menu'] = 'curation';
		}

		return $args;

	}

	/**
	 * Move the Carousal Taxonomy menu item to be a submenu item of "Curation".
	 */
	public function move_carousal_taxonomy_menu() {

		global $submenu;

		$pmc_carousel = \PMC_Carousel::get_instance();

		// Block to move Carousal submenu after Zones submenu in Curation menu.
		if ( ! empty( $submenu['curation'] ) ) {

			$curation_menus = $submenu['curation'];

			foreach ( $submenu['curation'] as $key => $val ) {

				if ( ! empty( $val[0] ) && 'Carousel' === $val[0] ) {

					$item = $curation_menus[ $key ];

					unset( $curation_menus[ $key ] );

					array_push( $curation_menus, $item );

					break;
				}
			}

			$submenu['curation'] = $curation_menus;
		}

		$carousel_modules = remove_submenu_page( 'edit.php', 'carousel-taxonomy.php' );

		if ( ! empty( $carousel_modules ) ) {

			add_submenu_page(
				'curation',
				'Carousel Taxonomies',
				'Carousel Taxonomies',
				'manage_options',
				'carousel-taxonomy.php',
				array(
					$pmc_carousel,
					'settings_page',
				)
			);

		}

		$carousel_taxonomy = 'edit-tags.php?taxonomy=' . $pmc_carousel::modules_taxonomy_name;
		$taxonomy_page     = remove_submenu_page( 'edit.php', $carousel_taxonomy );

		if ( ! empty( $taxonomy_page ) ) {

			add_submenu_page(
				'curation',
				'Carousel Modules',
				'Carousel Modules',
				'manage_options',
				$carousel_taxonomy
			);

		}
	}

	/**
	 * Change parent menu slug when Carousal Taxonomy page is open.
	 *
	 * @param string $parent_file Parent menu slug.
	 *
	 * @return string Parent menu slug.
	 */
	public function maybe_highlight_carousal_taxonomy_menu( $parent_file ) {

		$screen = get_current_screen();

		if ( ! empty( $screen->taxonomy ) && ( \PMC_Carousel::modules_taxonomy_name === $screen->taxonomy ) ) {
			$parent_file = 'curation';
		}

		return $parent_file;
	}
}
