<?php

/**
 * Footer Template.
 *
 * @package pmc-variety
 */

$footer = PMC\Core\Inc\Larva::get_instance()->get_json('modules/footer-simplified.vip');

// Main footer menu.
$footer_menus_data = PMC\Core\Inc\Menu::get_instance()->get_menu_data('pmc_variety_footer_simplified');
if (is_array($footer_menus_data) && isset($footer_menus_data['root'])) {
	$o_nav['o_nav_list_items'] = [];
	foreach ($footer_menus_data['root'] as $menu_item) {
		// Removing dummy data from menu item.
		$nav_list_item['c_link_text']    = $menu_item['c_nav_link_text'];
		$nav_list_item['c_link_url']     = $menu_item['c_nav_link_url'];
		$nav_list_item['c_link_classes'] = $footer['footer_link_classes'];
		$nav_list_items[]                = $nav_list_item;
	}
}
if (!empty($o_nav)) {
	$footer['o_nav']['o_nav_list_items'] = $nav_list_items;
}

// translators: %1$s current year.
$copy_text = sprintf(esc_html__('&copy; Copyright %1$s - Penske Business Media, LLC', 'pmc-variety'), date('Y'));

$footer['c_tagline_copyright']['c_tagline_markup'] = sprintf('%1$s', $copy_text);

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/footer-simplified.php', untrailingslashit(CHILD_THEME_PATH)),
	$footer,
	true
);
