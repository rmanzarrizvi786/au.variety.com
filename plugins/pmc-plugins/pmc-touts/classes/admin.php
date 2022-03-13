<?php
namespace PMC\Touts;

use PMC\Global_Functions\Traits\Singleton;

class Admin {
	use Singleton;

	protected function __construct() {
		add_action( 'admin_menu', array( $this, 'fix_menu_position' ) );
	}

	/**
	 * When registering a post type, the 'menu_position' field must be an
	 * integer. This doesn't give us as much control as we'd like (nor is it
	 * necessary), so we set the menu positions as strings in the
	 * `register_post_type()` call. WordPress will ignore them and choose its
	 * own menu position, and now here we change that position to where we
	 * intended.
	 */
	public function fix_menu_position() {
		global $menu;
		foreach ( $menu as $i => $menu_item ) {
			if ( 'edit.php?post_type=' . Tout::POST_TYPE_NAME === $menu_item[2] ) {
				$obj = get_post_type_object( Tout::POST_TYPE_NAME );
				if ( ! empty( $obj->menu_position ) && $obj->menu_position !== $menu_item[2] ) {
					$menu[ $obj->menu_position ] = $menu[ $i ];
					unset( $menu[ $i ] );
				}
				return;
			}
		}
	}

}
