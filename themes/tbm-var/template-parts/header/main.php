<?php

/**
 * Header Template.
 *
 * @package pmc-variety
 */

if (is_front_page()) {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json('modules/header.homepage');
} else {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json('modules/header.prototype');
}

// Sticky items.
$data['header_sticky']['c_logo']['c_logo_url'] = '/';

$data['header_sticky']['login_actions']['cxense_header_subscribe_module']                       = false; // don't show on sticky header
$data['header_sticky']['login_actions']['header_login_button']['header_button_url']             = '/digital-subscriber-access/#r=/print-plus/';
$data['header_sticky']['login_actions']['header_login_button']['header_button_id_attr']         = 'vy_head_login';
$data['header_sticky']['login_actions']['o_icon_button_login']['o_icon_button']['o_button_url'] = '/digital-subscriber-access/#r=/print-plus/';

// Login Actions.

/**
 * Backend notes:
 *
 * For each $data[header_main][login_actions] and $data[header_sticky][login_actions],
 *  update the values in these properties as follows:
 * - c_tagline.c_tagline_text => translated "Variety Print Plus Subscriber"
 * - c_link.c_link_text => user name, if its easy to access. if not, set c_link to false
 * - foreach o_drop_menu.o_nav.o_nav_list_items as c_link,
 *      c_link.c_link_text and c_link.c_link_url => logged in menu items
 *
 * Larva module to reference:
 * - http://localhost:3000/project/modules/header/
 *
 * For the VIP verison - all the same stuff should work, but haven't tested it yet.
 */

$data_fill = [
	[
		'modifier_class'     => '',
		'c_link_classes'     => 'lrv-a-unstyle-link u-colors-map-accent-b-100-b:hover vy-contact-us',
		'c_link_text'        => __('Print Plus Features', 'pmc-variety'),
		'c_link_url'         => '/print-plus/#r=/print-plus/',
		'c_link_rel_attr'    => false,
		'c_link_target_attr' => false,
	],
	[
		'modifier_class'     => '',
		'c_link_classes'     => 'lrv-a-unstyle-link u-colors-map-accent-b-100-b:hover vy-logout',
		'c_link_text'        => __('Logout', 'pmc-variety'),
		'c_link_url'         => '/digital-subscriber-access/#action=logout',
		'c_link_rel_attr'    => false,
		'c_link_target_attr' => false,
	],
	[
		'modifier_class'     => '',
		'c_link_classes'     => 'lrv-a-unstyle-link u-colors-map-accent-b-100-b:hover vy-help',
		'c_link_text'        => __('Help', 'pmc-variety'),
		'c_link_url'         => '/static-pages/help/',
		'c_link_rel_attr'    => false,
		'c_link_target_attr' => false,
	],
];
$data['header_sticky']['login_actions']['o_drop_menu']['o_nav']['o_nav_list_items']             = $data_fill;
$data['header_sticky']['login_actions']['o_drop_menu_mobile_icon']['o_nav']['o_nav_list_items'] = $data_fill;
$data['header_main']['login_actions']['o_drop_menu']['o_nav']['o_nav_list_items']               = $data_fill;
$data['header_main']['login_actions']['o_drop_menu_mobile_icon']['o_nav']['o_nav_list_items']   = $data_fill;
$data['header_main']['login_actions_mobile']['o_drop_menu']['o_nav']['o_nav_list_items']        = $data_fill;

$data['header_main']['login_actions']['o_drop_menu']['c_span_user']['c_span_classes'] .= ' vy-username ';
$data['header_main']['login_actions']['o_drop_menu']['c_span_user']['c_span_text']     = '';

$data['header_main']['login_actions']['o_drop_menu_mobile_icon']['c_span_user']['c_span_classes'] .= ' vy-username ';
$data['header_main']['login_actions']['o_drop_menu_mobile_icon']['c_span_user']['c_span_text']     = '';

$data['header_main']['login_actions_mobile']['o_drop_menu']['c_span_user']['c_span_classes'] .= ' vy-username ';
$data['header_main']['login_actions_mobile']['o_drop_menu']['c_span_user']['c_span_text']     = '';

// $data['header_main']['login_actions']['o_drop_menu']['o_nav_not_logged_in_vip']['o_nav_list_items'][0]['c_link_url'] = pmc_subscription_get_login_url();
$data['header_main']['login_actions']['o_drop_menu']['o_nav_not_logged_in_vip']['o_nav_list_items'][1]['c_link_url'] = '/vip-subscribe/?utm_source=site&utm_medium=VIP_TopNav&utm_campaign=VIPShop';

// $data['header_main']['login_actions']['o_drop_menu_mobile_icon']['o_nav_not_logged_in_vip']['o_nav_list_items'][0]['c_link_url'] = pmc_subscription_get_login_url();
$data['header_main']['login_actions']['o_drop_menu_mobile_icon']['o_nav_not_logged_in_vip']['o_nav_list_items'][1]['c_link_url'] = '/vip-subscribe/?utm_source=site&utm_medium=VIP_TopNav&utm_campaign=VIPShop';

// $data['header_main']['login_actions_mobile']['o_drop_menu']['o_nav_not_logged_in_vip']['o_nav_list_items'][0]['c_link_url'] = pmc_subscription_get_login_url();
$data['header_main']['login_actions_mobile']['o_drop_menu']['o_nav_not_logged_in_vip']['o_nav_list_items'][1]['c_link_url'] = '/vip-subscribe/?utm_source=site&utm_medium=VIP_TopNav&utm_campaign=VIPShop';

// $data['header_sticky']['login_actions']['o_drop_menu']['o_nav_not_logged_in_vip']['o_nav_list_items'][0]['c_link_url'] = pmc_subscription_get_login_url();
$data['header_sticky']['login_actions']['o_drop_menu']['o_nav_not_logged_in_vip']['o_nav_list_items'][1]['c_link_url'] = '/vip-subscribe/?utm_source=site&utm_medium=VIP_TopNav&utm_campaign=VIPShop';

// $data['header_sticky']['login_actions']['o_drop_menu_mobile_icon']['o_nav_not_logged_in_vip']['o_nav_list_items'][0]['c_link_url'] = pmc_subscription_get_login_url();
$data['header_sticky']['login_actions']['o_drop_menu_mobile_icon']['o_nav_not_logged_in_vip']['o_nav_list_items'][1]['c_link_url'] = '/vip-subscribe/?utm_source=site&utm_medium=VIP_TopNav&utm_campaign=VIPShop';

// $data['header_sticky']['login_actions']['o_drop_menu']['c_link']['c_link_classes'] .= ' vy-username ';
$data['header_sticky']['login_actions']['o_drop_menu']['c_link']['c_link_text']     = '';

// $data['header_sticky']['login_actions']['o_drop_menu_mobile_icon']['c_link']['c_link_classes'] .= ' vy-username ';
$data['header_sticky']['login_actions']['o_drop_menu_mobile_icon']['c_link']['c_link_text']     = '';

// Read next.
$next_post = \PMC\Core\Inc\Theme::get_instance()->get_random_recent_post();

if (!empty($next_post)) {
	$data['header_sticky']['o_icon_button_link']['c_span']['c_span_text'] = sprintf(
		'%s: %s',
		__('Read Next', 'pmc-variety'),
		get_the_title($next_post)
	);
} else {
	$data['header_sticky']['o_icon_button_link']['c_span'] = false;
}


$data['header_sticky']['o_icon_button_link']['o_button_url'] = get_the_permalink($next_post);

// Main header.
$data['header_main']['c_logo']['c_logo_url']          = '/';
$data['header_main']['header_main_show_special_icon'] = '2020' === date('Y');
$data['header_main']['c_logo']['c_logo_is_h1']        = is_home();

// Subscribe / login nav.
$data['header_main']['login_actions']['header_subscribe_button']['header_button_url']     = '/subscribe-us/?utm_source=site&utm_medium=VAR_TopNav&utm_campaign=DualShop';
$data['header_main']['login_actions']['header_subscribe_button']['header_button_id_attr'] = 'vy_head_subscribe';
$data['header_main']['login_actions']['header_login_button']['header_button_url']         = '/digital-subscriber-access/#r=/print-plus/';
$data['header_main']['login_actions']['header_login_button']['header_button_id_attr']     = 'vy_head_login';


$data['header_main']['login_actions_mobile']['cxense_header_subscribe_module']['cxense_id_attr'] = 'cx-module-header-link-mobile-vy';
$data['header_main']['login_actions_mobile']['header_login_button']['header_button_url']         = '/digital-subscriber-access/#r=/print-plus/';
$data['header_main']['login_actions_mobile']['header_login_button']['header_button_id_attr']     = 'vy_head_login';

if (is_single() && \Variety\Inc\Article::get_instance()->is_article_vip(get_the_ID())) {
	$data['header_main']['login_actions']['header_login_button']['header_button_url']        = pmc_subscription_get_login_url();
	$data['header_main']['login_actions_mobile']['header_login_button']['header_button_url'] = pmc_subscription_get_login_url();
}

// VIP link.
$data['header_main']['c_link']['c_link_url'] = \Variety\Plugins\Variety_VIP\VIP::vip_url();

// Mobile subscribe items.
$template = null; // $data['header_main']['o_nav']['o_nav_list_items'][0];

$data['header_main']['o_nav']['o_nav_list_items'] = [];

$menu_items = [
	[
		'label' => __('Subscribe', 'pmc-variety'),
		'url'   => 'https://thebrag.com/observer/entertainment-biz/',
	],
	[
		'label' => __('Login', 'pmc-variety'),
		'url'   => wp_login_url(),
	],
];

foreach ($menu_items as $menu_item) {
	$item = $template;

	$item['c_link_text'] = $menu_item['label'];
	$item['c_link_url']  = $menu_item['url'];

	$data['header_main']['o_nav']['o_nav_list_items'][] = $item;
}

// Top navigation.
$template = $data['header_main']['o_top_nav']['o_nav_list_items'][0];

$data['header_main']['o_top_nav']['o_nav_list_items'] = [];

$menu_items = [
	[
		'label' => __('Have a News Tip?', 'pmc-variety'),
		'url'   => 'https://thebrag.com/media/submit-a-tip/',
		'target' => '_blank',
	],
	[
		'label' => __('Newsletters', 'pmc-variety'),
		'url'   => 'https://thebrag.com/observer/entertainment-biz/',
		'target' => '_blank',
	],
];

foreach ($menu_items as $menu_item) {
	$item = $template;

	$item['c_link_text'] = $menu_item['label'];
	$item['c_link_url']  = $menu_item['url'];
	$item['c_link_target_attr']  = $menu_item['target'];

	$data['header_main']['o_top_nav']['o_nav_list_items'][] = $item;
}

// Regions.
// $data['header_main']['region_selector']['region_selector']['us_url']            = '/';
// $data['header_main']['region_selector_mobile']['region_selector']['us_url']     = '/';
// $data['header_main']['region_selector']['region_selector']['asia_url']          = '/c/asia/';
// $data['header_main']['region_selector_mobile']['region_selector']['asia_url']   = '/c/asia/';
// $data['header_main']['region_selector']['region_selector']['global_url']        = '/c/global/';
// $data['header_main']['region_selector_mobile']['region_selector']['global_url'] = '/c/global/';

$data['header_main']['region_selector']['region_selector']['us_url']            = 'https://variety.com/';
$data['header_main']['region_selector']['region_selector']['us_url_target']     = '_blank';
$data['header_main']['region_selector_mobile']['region_selector']['us_url']     = 'https://variety.com/';
$data['header_main']['region_selector_mobile']['region_selector']['us_url_target']     = '_blank';

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/header.php', untrailingslashit(CHILD_THEME_PATH)),
	$data,
	true
);
