<?php
/**
 * Header Template.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/header-sticky.variety-vip' );

if ( is_single() ) {
	$o_icon_button_screen_reader_text = get_the_title();
} elseif ( is_archive() ) {
	$queried_object = get_queried_object();
	if ( ! empty( $queried_object->labels->archives ) ) {
		$o_icon_button_screen_reader_text = $queried_object->labels->archives;
	}
}

// Mobile Top Nav Subscribe URL
$data['o_nav']['o_nav_list_items'][0]['c_link_url'] = '/vip-subscribe/?utm_source=site&utm_medium=VIP_TopNav&utm_campaign=VIPShop';

// Mobile Top Nav Login URL
$data['o_nav']['o_nav_list_items'][1]['c_link_url']  = pmc_subscription_get_login_url();
$data['o_nav']['o_nav_list_items'][1]['c_link_text'] = 'Log in';

$data['c_logo']['c_logo_url'] = '/';

$data['o_icon_button_link']['o_button_url']                     = \Variety\Plugins\Variety_VIP\VIP::vip_url();
$data['o_icon_button_link']['o_icon_button_screen_reader_text'] = $o_icon_button_screen_reader_text;

$data['o_icon_button_backup']['o_button_url']                     = \Variety\Plugins\Variety_VIP\VIP::vip_url();
$data['o_icon_button_backup']['o_icon_button_screen_reader_text'] = $o_icon_button_screen_reader_text;
$data['o_icon_button_backup']['c_icon']['c_icon_classes']         = 'c-icon lrv-u-display-block lrv-u-width-75p u-height-25 u-width-500@desktop-xl';

$data['login_actions']['cxense_header_subscribe_module']['cxense_id_attr'] = 'cx-module-header-link-vip';
$data['login_actions']['header_login_button']['header_button_url']         = pmc_subscription_get_login_url();
$data['login_actions']['header_login_button']['header_button_id_attr']     = 'vy_head_login';

$data['header_vip']['o_nav_secondary']['o_nav_list_items'][0]['c_link_url']     = '/tips/';
$data['header_vip']['o_nav_secondary']['o_nav_list_items'][1]['c_link_url']     = pmc_subscription_get_login_url();
$data['header_vip']['o_nav_secondary']['o_nav_list_items'][1]['modifier_class'] = 'cx-module-header-link-vip';

$data['is_vip_header_h1'] = is_page( 'vip' ) || is_archive();

$data['login_actions']['o_login_icon']['o_icon_button']['o_button_url'] = pmc_subscription_get_login_url();

$data_fill = [
	[
		'modifier_class'     => '',
		'c_link_classes'     => 'lrv-a-unstyle-link vy-vip-logout lrv-u-display-none subscriber-logout-link',
		'c_link_text'        => __( 'Logout', 'pmc-variety' ),
		'c_link_url'         => pmc_subscription_get_logout_url(),
		'c_link_rel_attr'    => 'nofollow',
		'c_link_target_attr' => false,
	],
	[
		'modifier_class'     => '',
		'c_link_classes'     => 'lrv-a-unstyle-link vy-vip-help',
		'c_link_text'        => __( 'Faq', 'pmc-variety' ),
		'c_link_url'         => '/vip-faq/',
		'c_link_rel_attr'    => false,
		'c_link_target_attr' => false,
	],
];

$user_data = \Variety\Plugins\Variety_VIP\VIP::get_instance()->get_user_data();

if ( ! empty( $user_data->acct->name ) ) {
	$data['login_actions']['o_drop_menu']['c_span_user']['c_span_text']             = $user_data->acct->name;
	$data['login_actions']['o_drop_menu_mobile_icon']['c_span_user']['c_span_text'] = $user_data->acct->name;
} else {
	$data['login_actions']['o_drop_menu']['c_span_user']['c_span_text']             = '';
	$data['login_actions']['o_drop_menu_mobile_icon']['c_span_user']['c_span_text'] = '';
}

$data['login_actions']['o_drop_menu']['c_tagline']['c_tagline_text']           = __( 'VIP Subscriber', 'pmc-variety' );
$data['login_actions']['o_drop_menu']['o_nav']['o_nav_list_items']             = $data_fill;
$data['login_actions']['o_drop_menu_mobile_icon']['o_nav']['o_nav_list_items'] = $data_fill;
$data['login_actions']['o_drop_menu']['c_span_user']['c_span_classes']             .= ' vy-vip-username ';
$data['login_actions']['o_drop_menu_mobile_icon']['c_span_user']['c_span_classes'] .= ' vy-vip-username ';

// Header menu.
$menu = PMC\Core\Inc\Menu::get_instance()->get_menu_data( 'pmc_variety_vip_header' );

// Header Navbar menu.
$navbar_menu = PMC\Core\Inc\Menu::get_instance()->get_menu_data( 'pmc_variety_vip_header_navbar' );

$template = $data['header_vip']['o_nav_primary']['o_nav_list_items'][0];
$back     = $data['header_vip']['o_nav_primary']['o_nav_list_items'][3];

$back['c_link_url'] = '/';

// Empty template items.
$data['header_vip']['o_nav_primary']['o_nav_list_items'] = [];

if ( ! empty( $menu['root'] ) ) {
	foreach ( $menu['root'] as $menu_item ) {
		$item = $template;

		$item['c_link_text'] = $menu_item['c_nav_link_text'];
		$item['c_link_url']  = $menu_item['c_nav_link_url'];

		$data['header_vip']['o_nav_primary']['o_nav_list_items'][] = $item;
	}
}

$navbar_template = $data['header_vip_navbar']['o_nav']['o_nav_list_items'][0];
$current_slug    = get_queried_object()->slug;

$data['header_vip_navbar']['o_nav']['o_nav_list_items'] = [];

if ( ! empty( $navbar_menu['root'] ) ) {
	foreach ( $navbar_menu['root'] as $menu_item ) {
		$item = $navbar_template;

		$item['c_link_text'] = $menu_item['c_nav_link_text'];
		$item['c_link_url']  = $menu_item['c_nav_link_url'];

		// Make link of the current page visible( make the text white ).
		if ( $current_slug === $menu_item['slug'] ) {
			$item['c_link_classes'] .= 'lrv-u-color-white';
		}

		$data['header_vip_navbar']['o_nav']['o_nav_list_items'][] = $item;
	}
}

$data['header_vip']['o_nav_primary']['o_nav_list_items'][] = $back;
$data['header_sticky_secondary_classes']                  .= ' vip-login-bar ';

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/header-sticky.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
