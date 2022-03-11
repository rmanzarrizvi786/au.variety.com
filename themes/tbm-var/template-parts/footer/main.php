<?php

/**
 * Footer Template.
 *
 * @package pmc-variety
 */

$footer = PMC\Core\Inc\Larva::get_instance()->get_json('modules/footer.prototype');

// Latest issue.
$issue = null; // Variety_Digital_Feed::get_instance()->get_latest_variety_issue();

if (!empty($issue)) {
	$footer['c_lazy_image']['c_lazy_image_link_url']        = 'https://subscribe.variety.com/site/magazine-subscribe-choice';
	$footer['c_lazy_image']['c_lazy_image_src_url']         = $issue['img320'];
	$footer['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$footer['c_lazy_image']['c_lazy_image_srcset_attr']     = '';
}

// Subscribe Link
$footer['c_link_subscribe']['c_link_url'] = 'https://subscribe.variety.com/site/magazine-subscribe-choice';

// Logo URL.
$footer['c_logo']['c_logo_url'] = '/';

// $footer_tip_data = PMC\Core\Inc\Theme::get_instance()->get_tip_page_url();

// $footer['footer_tip']['c_button']['c_button_url'] = $footer_tip_data;

// Main footer menu.
$footer_menus_data = PMC\Core\Inc\Menu::get_instance()->get_menu_data('pmc_variety_footer');

// Social menu.
$social_menu = PMC\Core\Inc\Menu::get_instance()->get_menu_data('pmc_variety_social');

if (!empty($social_menu['root']) && is_array($social_menu['root'])) {
	$key = key($social_menu['root']);

	$footer_menus_data['root'][] = [
		'id'              => $key,
		'c_nav_link_text' => __('Connect', 'pmc-variety'),
		'child'           => $social_menu['root'],
	];
}

// Using object structure from prototype to avoid removing default values and properties.
$o_nav_item_prototype = $footer['footer_menus']['o_navs'][0];
$o_nav_list_prototype = $footer['footer_menus']['o_navs'][0]['o_nav_list_items'][0];

if (is_array($footer_menus_data) && isset($footer_menus_data['root'])) {
	foreach ($footer_menus_data['root'] as $key => $val) {

		$footer_menus['o_navs'][$key]                     = $o_nav_item_prototype;
		$footer_menus['o_navs'][$key]['o_nav_title_text'] = $val['c_nav_link_text'];

		// Hide the first menu h4 title.
		if (1 === count($footer_menus['o_navs'])) {
			$footer_menus['o_navs'][$key]['o_nav_title_classes'] = 'lrv-js-MobileHeightToggle-trigger lrv-u-padding-lr-1 u-padding-lr-00@tablet u-padding-lr-1@desktop u-padding-b-050@mobile-max lrv-u-margin-tb-00 lrv-u-width-100p@mobile-max lrv-a-icon-after a-icon-down-caret lrv-a-icon-after:margin-l-auto lrv-a-icon-after-remove@tablet lrv-a-icon-invert u-cursor-pointer@mobile-max lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-18 lrv-u-font-size-14@tablet u-border-b-1@mobile-max u-border-color-chateau-grey a-hidden@tablet';
		} else {
			$footer_menus['o_navs'][$key]['o_nav_title_classes'] = 'lrv-js-MobileHeightToggle-trigger lrv-u-padding-lr-1 u-padding-lr-00@tablet u-padding-lr-1@desktop u-padding-b-050@mobile-max lrv-u-margin-tb-00 lrv-u-width-100p@mobile-max lrv-a-icon-after a-icon-down-caret lrv-a-icon-after:margin-l-auto lrv-a-icon-after-remove@tablet lrv-a-icon-invert u-cursor-pointer@mobile-max lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-18 lrv-u-font-size-14@tablet u-border-b-1@mobile-max u-border-color-chateau-grey';
		}

		// Removing dummy data from menu item.
		$footer_menus['o_navs'][$key]['o_nav_list_items'] = [];

		if ($val['child']) {
			foreach ($val['child'] as $child) {

				$nav_list_item = $o_nav_list_prototype;

				// Note: c_nav_link is hardcoded is coming from pmc-core-v2
				$nav_list_item['c_link_text']     = $child['c_nav_link_text'];
				$nav_list_item['c_link_url']      = $child['c_nav_link_url'];
				$nav_list_item['c_link_classes'] .= ' ' . $child['c_nav_link_classes'];

				$footer_menus['o_navs'][$key]['o_nav_list_items'][] = $nav_list_item;
			}
		}
	}
}

if (!empty($footer_menus)) {
	$footer['footer_menus']['o_navs'] = $footer_menus['o_navs'];
}

// translators: %1$s current year.
$copy_text = sprintf(esc_html__('Variety is a part of Penske Media Corporation. &copy; %1$s Variety Media, LLC. All Rights Reserved. Variety and the Flying V logos are trademarks of Variety Media, LLC.', 'pmc-variety'), gmdate('Y'));

// translators: %1$s WordPress.com VIP URL.
$powered_text = sprintf(esc_html__('Powered by %1$s', 'pmc-variety'), '<a href="https://vip.wordpress.com/?utm_source=vip_powered_wpcom&amp;utm_medium=web&amp;utm_campaign=VIP%20Footer%20Credit" class="lrv-u-color-white lrv-u-color-brand-primary:hover">WordPress.com VIP</a>');

$footer['c_tagline_copyright']['c_tagline_markup'] = sprintf('%1$s<br>%2$s', $copy_text, $powered_text);

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/footer.php', untrailingslashit(CHILD_THEME_PATH)),
	$footer,
	true
);

\PMC::render_template(
	sprintf('%s/template-parts/footer/mega-menu.php', untrailingslashit(CHILD_THEME_PATH)),
	$footer,
	true
);
