const clonedeep = require( 'lodash.clonedeep' );
const header_login_button = clonedeep( require( '../header-button/header-button.prototype' ) );
const cxense_header_subscribe_widget = clonedeep( require( '../cxense-widget/cxense-widget.prototype' ) );
const o_drop_menu = clonedeep( require( '../../objects/o-drop-menu-login/o-drop-menu-login.prototype' ) );
const o_login_icon = clonedeep( require( '../../objects/o-login-icon/o-login-icon.prototype' ) );
const c_span_user = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype.js' ) );
const c_tagline = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype.js' ) );

cxense_header_subscribe_widget.cxense_id_attr = 'cx-module-header-link-vy';

o_drop_menu.o_drop_menu_classes += ' lrv-u-whitespace-nowrap lrv-u-border-color-grey lrv-u-font-family-secondary';
o_drop_menu.o_drop_menu_list_classes += ' lrv-u-margin-t-050 lrv-a-glue lrv-a-glue--r-0 lrv-u-width-300 lrv-u-background-color-white lrv-u-border-a-1 lrv-u-padding-a-1 u-background-color-accent-c-40';
o_drop_menu.o_drop_menu_toggle_classes += ' u-border-color-brand-secondary-30 lrv-u-text-align-center lrv-u-padding-tb-025 lrv-u-padding-r-050 lrv-u-line-height-normal lrv-u-display-inline-block lrv-u-border-a-1';

o_drop_menu.c_span.c_span_text = 'Log in';
o_drop_menu.c_span.c_span_classes = 'lrv-u-text-transform-uppercase lrv-u-font-weight-bold lrv-u-font-size-12 lrv-u-color-white u-letter-spacing-001 lrv-u-margin-l-050 u-color-brand-primary-40:hover';

o_drop_menu.c_span_logged_in.c_span_text = 'Account';
o_drop_menu.c_span_logged_in.c_span_classes = 'lrv-u-text-transform-uppercase lrv-u-font-weight-bold lrv-u-font-size-12 lrv-u-color-white u-letter-spacing-001 lrv-u-margin-l-050 u-color-brand-primary-40:hover';

// Disable the icon button
// We still need to show this as per mocks
// Something to re-evaluate
o_login_icon.o_icon_button.o_icon_button_url = false;
o_login_icon.o_icon_button.o_icon_button_screen_reader_text = '';
o_login_icon.o_icon_button.c_span.c_span_text = '';

c_span_user.c_span_classes = 'lrv-u-font-size-16 lrv-u-font-weight-bold lrv-u-text-transform-uppercase lrv-u-color-grey-dark';
c_span_user.c_span_text = 'Awallenstein';

c_tagline.c_tagline_classes = 'lrv-u-margin-tb-00 lrv-u-padding-t-025 lrv-u-padding-b-1 lrv-u-font-size-14 lrv-u-color-grey-dark';
c_tagline.c_tagline_text = 'Variety Print Plus Subscriber';

o_drop_menu.c_span_user = c_span_user;
o_drop_menu.c_tagline = c_tagline;

// (Not) Logged In Menu
const o_nav_not_logged_in_list_classes = ' lrv-u-font-size-14 lrv-u-font-weight-bold lrv-u-color-grey-dark lrv-u-padding-b-050 u-colors-map-accent-b-100-b:hover lrv-u-display-block';
o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_item_classes += o_nav_not_logged_in_list_classes;
o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_item_classes += o_nav_not_logged_in_list_classes;
o_drop_menu.o_nav_logged_in_vip.o_nav_list_item_classes += o_nav_not_logged_in_list_classes;

// Not Logged in to Print Plus
o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_items = o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_items.slice( 0, 2 );
o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_items[0].c_link_url = '/digital-subscriber-access/#r=/print-plus/';
o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_items[0].c_link_text = 'Print Plus Login';
o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_items[0].c_link_classes = ' u-colors-map-accent-b-100-b:hover lrv-u-display-block';
o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_items[1].c_link_url = '/variety-magazine-subscribe/';
o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_items[1].c_link_text = 'Subscribe to Print Plus';
o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_items[1].c_link_classes = ' u-colors-map-accent-b-100-b:hover lrv-u-display-block';

// Not Logged in to VIP
o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_items = o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_items.slice( 0, 2 );
o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_items[0].c_link_url = '#';
o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_items[0].c_link_text = 'VIP Login';
o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_items[0].c_link_classes += ' u-colors-map-accent-b-100-b:hover lrv-u-display-block';
o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_items[1].c_link_url = '#';
o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_items[1].c_link_text = 'Subscribe to VIP';
o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_items[1].c_link_classes += ' u-colors-map-accent-b-100-b:hover lrv-u-display-block';

// Logged in to VIP
o_drop_menu.o_nav_logged_in_vip.o_nav_list_items = o_drop_menu.o_nav_logged_in_vip.o_nav_list_items.slice( 0, 1 );
o_drop_menu.o_nav_logged_in_vip.o_nav_list_items[0].c_link_url = '/vip/';
o_drop_menu.o_nav_logged_in_vip.o_nav_list_items[0].c_link_text = 'Access your VIP account';

o_drop_menu.o_nav.o_nav_list_item_classes += ' lrv-u-font-size-14 lrv-u-font-weight-bold lrv-u-color-grey-dark lrv-u-padding-b-050 lrv-u-display-block';
o_drop_menu.c_horizontal_rule_display = true;

const o_drop_menu_mobile_icon = clonedeep( o_drop_menu );
o_drop_menu_mobile_icon.c_span = false;
o_drop_menu_mobile_icon.o_icon_button = o_login_icon.o_icon_button;
o_drop_menu_mobile_icon.o_drop_menu_toggle_classes = '';
o_drop_menu_mobile_icon.o_drop_menu_classes += ' a-hidden@desktop a-hidden@tablet';

module.exports = {
	o_login_icon,
	header_login_button,
	cxense_header_subscribe_widget,
	o_drop_menu,
	o_drop_menu_mobile_icon
};
