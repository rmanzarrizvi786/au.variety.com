const clonedeep = require( 'lodash.clonedeep' );

const header_sticky_prototype = require( '../header-sticky/header-sticky.prototype.js' );
const header_sticky = clonedeep( header_sticky_prototype );

const login_actions_mobile = clonedeep( require( '../login-actions-mobile/login-actions-mobile.variety-vip' ) );

const o_nav = clonedeep( require( '@penskemediacorp/larva-patterns/objects/o-nav/o-nav.horizontal.js' ) );

o_nav.o_nav_list_items = o_nav.o_nav_list_items.slice( 0, -1 );
o_nav.o_nav_list_classes = o_nav.o_nav_list_classes.replace( 'lrv-a-space-children--1', 'a-space-children--025' );
o_nav.o_nav_list_items.forEach( item => {
	item.c_link_classes = 'lrv-u-color-white lrv-u-font-family-secondary lrv-u-font-size-10 u-color-brand-accent-20:hover u-padding-r-025 u-border-r-1 lrv-u-border-color-white';
} );

o_nav.o_nav_list_items[0].c_link_url = '/subscribe-us/';
o_nav.o_nav_list_items[0].c_link_text = 'Subscribe';
o_nav.o_nav_list_items[1].c_link_url = '/digital-subscriber-access/#r=/print-plus/';
o_nav.o_nav_list_items[1].c_link_text = 'Login';

const o_icon_button_prototype = require( '@penskemediacorp/larva-patterns/objects/o-icon-button/o-icon-button.prototype.js' );
const o_icon_button_backup = clonedeep( o_icon_button_prototype );

const header_vip_prototype = require( '../main-header-vip/main-header-vip.variety-vip.js' );
const header_vip = clonedeep( header_vip_prototype );

const login_actions_vip = clonedeep( require( '../login-actions-vip/login-actions-vip.prototype' ) );

const {
	c_logo,
	o_icon_button_menu,
	o_icon_button_link,
	expandable_search
} = header_sticky;

header_sticky.login_actions = login_actions_vip;

header_sticky.header_login_wrapper_classes = header_sticky.header_login_wrapper_classes.replace( 'lrv-u-margin-l-1', 'lrv-u-margin-l-050' );

header_sticky.header_sticky_classes = header_sticky.header_sticky_classes.replace( 'u-background-color-brand-accent-100-b', 'lrv-u-background-color-white' );
header_sticky.header_sticky_classes += ' lrv-u-width-100p u-background-image-slash lrv-a-glue-parent u-z-index-top';

header_sticky.header_sticky_inner_classes = header_sticky.header_sticky_inner_classes.replace( 'lrv-u-padding-lr-1@tablet', '' );

header_sticky.header_sticky_logo_classes = header_sticky.header_sticky_logo_classes.replace( 'u-max-width-205@tablet', 'u-max-width-90@tablet' );
header_sticky.header_sticky_logo_classes = header_sticky.header_sticky_logo_classes.replace( 'u-height-20', '' );
header_sticky.header_sticky_logo_classes += ' u-width-100p@tablet u-order-n1@tablet lrv-u-height-100p';

header_sticky.header_sticky_menu_classes = header_sticky.header_sticky_menu_classes.replace( 'u-margin-r-150@mobile-max', 'u-margin-r-075@mobile-max' );
header_sticky.header_sticky_menu_classes += ' a-glue-parent@tablet lrv-u-padding-l-1';

header_sticky.header_sticky_secondary_classes = header_sticky.header_sticky_secondary_classes.replace( 'lrv-u-justify-content-space-between', 'u-justify-content-end' );

c_logo.c_logo_classes += ' u-background-color-brand-accent-100-b a-hidden@mobile-max u-padding-lr-075@tablet u-width-115@tablet lrv-u-height-100p u-border-r-6@tablet lrv-u-border-color-brand-primary lrv-u-flex-shrink-0';

header_sticky.header_sticky_search_classes = header_sticky.header_sticky_search_classes.replace( 'u-margin-r-150', '' );
header_sticky.expandable_search.expandable_search_classes = header_sticky.expandable_search.expandable_search_classes.replace( 'u-background-color-brand-accent-100-b', '' );
header_sticky.expandable_search.expandable_search_classes = header_sticky.expandable_search.expandable_search_classes.replace( 'lrv-u-color-white', '' );

o_icon_button_menu.o_icon_button_classes = o_icon_button_menu.o_icon_button_classes.replace( 'u-color-brand-secondary-30', 'lrv-u-color-black' );
o_icon_button_menu.o_icon_button_classes = o_icon_button_menu.o_icon_button_classes.replace( 'u-color-brand-accent-20:hover', 'u-color-brand-primary:hover' );
o_icon_button_menu.o_icon_button_classes = o_icon_button_menu.o_icon_button_classes.replace( 'js-MegaMenu-Trigger', '' );

o_icon_button_link.o_icon_button_classes = o_icon_button_link.o_icon_button_classes.replace( 'a-hidden@mobile-max', '' );
o_icon_button_link.o_icon_button_classes = o_icon_button_link.o_icon_button_classes.replace( 'u-margin-l-4', '' );
o_icon_button_link.o_icon_button_classes += ' a-hidden@tablet';
o_icon_button_link.c_icon.c_icon_name = 'vip-plus';
o_icon_button_link.c_icon.c_icon_classes = 'u-width-65 u-height-20';
o_icon_button_link.c_span.c_span_classes += ' a-hidden@mobile-max';

header_sticky.login_actions.header_login_button.header_button_classes += ' lrv-u-margin-r-050';

header_sticky.login_actions.header_login_button.header_button_url = '/login/';

o_icon_button_backup.o_icon_button_classes += ' lrv-u-height-100p  lrv-u-flex@tablet lrv-u-width-100p lrv-u-align-items-center u-margin-l-075@tablet a-hidden@mobile-max';
o_icon_button_backup.c_span.c_span_classes = 'lrv-u-display-none';
o_icon_button_backup.c_icon.c_icon_name = 'vip-plus-large';
o_icon_button_backup.c_icon.c_icon_classes = 'lrv-u-display-block lrv-u-width-100p u-max-width-635 u-width-570@desktop u-width-640@desktop-xl u-height-25';
o_icon_button_backup.o_icon_button_url = true;
o_icon_button_backup.o_button_url = '#vip_home';

expandable_search.o_icon_button_search.o_icon_button_classes = expandable_search.o_icon_button_search.o_icon_button_classes.replace( 'lrv-u-color-white', 'lrv-u-color-black' );
expandable_search.o_icon_button_search.o_icon_button_classes = expandable_search.o_icon_button_search.o_icon_button_classes.replace( 'u-color-brand-accent-20:hover', 'u-color-brand-primary:hover' );

module.exports = {
	...header_sticky,
	o_nav: o_nav,
	o_icon_button_backup,
	is_vip_header: true,
	header_vip,
};
