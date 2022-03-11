<?php
/**
 * Front Page Trending Menu module.
 *
 * @package pmc-variety
 */

if ( has_nav_menu( 'pmc_variety_trending' ) ) {

	$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/trending-menu.prototype' );
	$menu = PMC\Core\Inc\Menu::get_instance()->get_menu_data( 'pmc_variety_trending' );

	$template = $data['o_nav']['o_nav_list_items'][0];

	$data['o_nav']['o_nav_list_items'] = [];

	if ( ! empty( $menu['root'] ) ) {
		foreach ( $menu['root'] as $menu_item ) {
			$item = $template;

			$item['c_link_text'] = $menu_item['c_nav_link_text'];
			$item['c_link_url']  = $menu_item['c_nav_link_url'];

			$data['o_nav']['o_nav_list_items'][] = $item;
		}

		\PMC::render_template(
			sprintf( '%s/template-parts/patterns/modules/trending-menu.php', untrailingslashit( CHILD_THEME_PATH ) ),
			$data,
			true
		);
	}

}
