<?php

use PMC\Core\Inc\Larva;

$data = PMC\Core\Inc\Larva::get_instance()->get_json('modules/mega-menu.prototype');

// Mega Menu.
$data['c_logo']['c_logo_url'] = '/';

// Mega menu menu.
$mega_menu = PMC\Core\Inc\Menu::get_instance()->get_menu_data('pmc_variety_mega');

$template       = $data['mega_menu_content']['mega_menu_content_items'][0];
$child_template = $data['mega_menu_content']['mega_menu_content_items'][0]['mega_menu_item_children'][0];

$data['mega_menu_content']['mega_menu_content_items'] = [];

if (is_array($mega_menu) && isset($mega_menu['root'])) {
	foreach ($mega_menu['root'] as $key => $val) {
		$item = $template;

		$item['c_link']['c_link_text']   = $val['c_nav_link_text'];
		$item['c_link']['c_link_url']    = $val['c_nav_link_url'];
		$item['mega_menu_item_children'] = [];

		if (isset($val['child'])) {
			foreach ($val['child'] as $child) {

				$child_item = $child_template;

				$child_item['c_link_text'] = $child['c_nav_link_text'];
				$child_item['c_link_url']  = $child['c_nav_link_url'];

				$item['mega_menu_item_children'][] = $child_item;
			}
		}

		$data['mega_menu_content']['mega_menu_content_items'][] = $item;
	}
}

// Mega email capture.
$data['mega_menu_footer']['o_email_capture_form']['o_email_capture_form_hidden_field_items'][1]['c_hidden_field_value_attr'] = date('Y-m-d');

// Mega Social List.
$social_menu = PMC\Core\Inc\Menu::get_instance()->get_menu_data('pmc_variety_social');
$template    = $data['mega_menu_footer']['o_social_list']['o_social_list_icons'][0];

$data['mega_menu_footer']['o_social_list']['o_social_list_icons'] = [];

if (!empty($social_menu['root'])) {
	foreach ($social_menu['root'] as $menu_item) {
		$item = $template;

		$the_domain = wp_parse_url(str_replace('www.', '', $menu_item['c_nav_link_url']));
		$icon       = strtolower(str_replace('.com', '', $the_domain['host']));

		$item['c_icon_name'] = $icon;
		$item['c_icon_url']  = $menu_item['c_nav_link_url'];

		$data['mega_menu_footer']['o_social_list']['o_social_list_icons'][] = $item;
	}
}

// Mega Footer.
$mega_menu = PMC\Core\Inc\Menu::get_instance()->get_menu_data('pmc_variety_mega_bottom');
$template  = $data['mega_menu_footer']['o_nav']['o_nav_list_items'][0];

$data['mega_menu_footer']['o_nav']['o_nav_list_items'] = [];

if (!empty($mega_menu['root'])) {
	foreach ($mega_menu['root'] as $menu_item) {
		$item = $template;

		$item['c_link_text'] = $menu_item['c_nav_link_text'];
		$item['c_link_url']  = $menu_item['c_nav_link_url'];

		$data['mega_menu_footer']['o_nav']['o_nav_list_items'][] = $item;
	}
}

// Mega PMC footer.
$data['mega_menu_footer']['c_icon']['c_icon_url']            = 'https://pmc.com/';
$data['mega_menu_footer']['mega_menu_footer_copyright_text'] = ''; // sprintf('Variety is a part of Penske Media Corporation. &copy; %1$s Variety Media, LLC. All Rights Reserved.', gmdate('Y'));

// Mega subscribe link.
$data['mega_menu_footer']['c_subscribe_link']['c_link_url']     = 'https://thebrag.com/observer/film-tv/?utm_source=varietyau&utm_medium=Mega';
$data['mega_menu_footer']['c_subscribe_link']['modifier_class'] = 'cx-module-header-link-vy';

// Mega Regions + tips.
$data['mega_menu_footer']['o_nav_tips']['o_nav_list_items'][0]['c_link_url']  = 'https://thebrag.com/media/submit-a-tip/';

// $data['mega_menu_footer']['region_selector']['region_selector']['us_url']     = '/';
// $data['region_selector_mobile']['region_selector']['us_url']                  = '/';
// $data['mega_menu_footer']['region_selector']['region_selector']['asia_url']   = '/c/asia/';
// $data['region_selector_mobile']['region_selector']['asia_url']                = '/c/asia/';
// $data['mega_menu_footer']['region_selector']['region_selector']['global_url'] = '/c/global/';
// $data['region_selector_mobile']['region_selector']['global_url']              = '/c/global/';


$data['region_selector_mobile']['region_selector']['us_url']                  = 'https://variety.com/';
$data['region_selector_mobile']['region_selector']['us_url_target']     = '_blank';
$data['mega_menu_footer']['region_selector']['region_selector']['us_url']                  = 'https://variety.com/';
$data['mega_menu_footer']['region_selector']['region_selector']['us_url_target']     = '_blank';


// Mega mobile nav.
$template = $data['mobile_navigation']['o_nav_list_items'][0];

$data['mobile_navigation']['o_nav_list_items'] = [];

$menu_items = [
	[
		'label' => __('Have a News Tip?', 'pmc-variety'),
		'url'   => 'https://thebrag.com/media/submit-a-tip/',
	],
	// [
	// 	'label' => __('Subscribe', 'pmc-variety'),
	// 	'url'   => '/subscribe-us/?utm_source=site&utm_medium=Mega',
	// ],
	[
		'label' => __('Newsletters', 'pmc-variety'),
		'url'   => 'https://thebrag.com/observer/film-tv/',
	],
];
if (is_user_logged_in()) {
	$current_url = home_url(add_query_arg([], $GLOBALS['wp']->request));
	$menu_items[] =
		[
			'label' => __('Logout', 'pmc-variety'),
			'url'   => esc_url(wp_logout_url($current_url)),
		];
}

foreach ($menu_items as $menu_item) {
	$item = $template;

	$item['c_link_text'] = $menu_item['label'];
	$item['c_link_url']  = $menu_item['url'];

	$data['mobile_navigation']['o_nav_list_items'][] = $item;
}

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/mega-menu.php', untrailingslashit(CHILD_THEME_PATH)),
	$data,
	true
);
