<?php
/**
 * Primary Menu Walker
 *
 * @package pmc-core-v2
 */

namespace PMC\Core\Inc\Menus;

class Primary_Menu_Walker extends \Walker_Nav_Menu {

	/**
	 * Base $db_fields. Necessary for Walker_Nav_Menu.
	 *
	 * @var array
	 */
	public $db_fields = array(
		'parent' => 'menu_item_parent',
		'id'     => 'db_id',
	);

	/**
	 * Beginning of a new element.
	 *
	 * @param string   &$output
	 * @param WP_Post  $item
	 * @param int      $depth Depth of menu item. Used for padding.
	 * @param stdClass $args  An object of wp_nav_menu() arguments.
	 * @param int      $id    Current item ID.
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$output .= sprintf( '<li class="site-header__link header-nav__link" data-uber-nav-target="%1$s"><a href="%2$s" class="c-nav__link">%3$s</a></li>',
			esc_attr( sanitize_title_with_dashes( $item->title ) ), esc_url( $item->url ), esc_html( $item->title ) );
	}
}
