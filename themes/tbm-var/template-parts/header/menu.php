<?php

/**
 * Main Menu Template.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json('modules/main-menu.prototype');

// Menu.
$header_menu = PMC\Core\Inc\Menu::get_instance()->get_menu_data('pmc_variety_header');

$template = $data['o_nav_icon']['o_nav_list_items'][0];
/* $vip      = end($data['o_nav_icon']['o_nav_list_items']);

// VIP link.
$vip['o_button_url'] = add_query_arg(
	[
		'cx_navsource' => 'vip-nav-link',
	],
	\Variety\Plugins\Variety_VIP\VIP::vip_url()
); */

// Empty template items.
$data['o_nav_icon']['o_nav_list_items'] = [];

if (!empty($header_menu['root'])) {
	foreach ($header_menu['root'] as $menu_item) {
		$item = $template;

		$item['o_icon']                = false;
		$item['c_span']['c_span_text'] = $menu_item['c_nav_link_text'];
		$item['o_button_url']          = $menu_item['c_nav_link_url'];

		$data['o_nav_icon']['o_nav_list_items'][] = $item;
	}
}

// $data['o_nav_icon']['o_nav_list_items'][] = $vip;

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/main-menu.php', untrailingslashit(CHILD_THEME_PATH)),
	$data,
	true
);
