const clonedeep = require( 'lodash.clonedeep' );

const header_main_prototype = require( '../header-main/header-main.prototype.js' );
const header_main = clonedeep( header_main_prototype );

const login_actions = clonedeep( require( '../login-actions/login-actions.prototype' ) );
const o_icon_button_prototype = require( '@penskemediacorp/larva-patterns/objects/o-icon-button/o-icon-button.prototype.js' );
const o_icon_button_link = clonedeep( o_icon_button_prototype );

header_main.expandable_search.o_icon_button_search.c_icon.c_icon_classes = 'lrv-u-display-block u-width-25 u-height-25 lrv-u-margin-a-050';

header_main.c_logo.c_logo_classes = header_main.c_logo.c_logo_classes.replace( 'u-width-205@tablet', 'u-width-90@tablet' );

const mega_menu_prototype = require( '../mega-menu/mega-menu.prototype.js' );
const mega_menu = clonedeep( mega_menu_prototype );

const { o_icon_button } = clonedeep( header_main.login_actions.o_login_icon );

header_main.o_icon_button_menu.o_icon_button_classes += ' u-width-40';

o_icon_button.o_icon_button_classes = o_icon_button.o_icon_button_classes.replace( 'a-hidden@tablet', '' );
o_icon_button.c_icon.c_icon_classes = o_icon_button.c_icon.c_icon_classes.replace( 'a-hidden@tablet', '' );
o_icon_button.c_span.c_span_classes = o_icon_button.c_span.c_span_classes.replace( 'a-hidden@mobile-max', 'lrv-u-display-none' );

header_main.o_top_nav.o_nav_list_items = header_main.o_top_nav.o_nav_list_items.slice( 1, -1 );
header_main.o_top_nav.o_nav_list_items.forEach( item => {
	item.c_link_classes = 'lrv-u-color-white lrv-u-font-family-secondary lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-letter-spacing-2 u-color-brand-accent-20:hover';
} );

o_icon_button_link.o_icon_button_url = true;
o_icon_button_link.o_button_url = '#read_next_url';
o_icon_button_link.o_icon_button_classes = 'a-hidden@mobile-max lrv-u-color-white lrv-u-font-family-secondary lrv-u-font-weight-bold u-color-brand-accent-20:hover u-font-size-15 u-margin-l-4';
o_icon_button_link.c_icon.c_icon_name = '';
o_icon_button_link.c_icon.c_icon_classes = 'lrv-u-display-none';
o_icon_button_link.c_span.c_span_text = 'Read Next: ‘Roxanne’ Singer Arizona Zervas Signs With Columbia';

login_actions.cxense_header_subscribe_widget.cxense_id_attr = '';

const header_vip_navbar = clonedeep( require( '../header-vip-navbar/header-vip-navbar.prototype.js' ) );

module.exports = {
	...header_main,
	header_sticky_classes: 'u-background-color-brand-accent-100-b lrv-u-width-100p',
	header_sticky_inner_classes: 'lrv-u-flex lrv-u-align-items-center lrv-u-justify-content-space-between u-height-45@tablet lrv-u-position-relative',
	header_sticky_logo_classes: 'u-margin-lr-auto@mobile-max u-margin-r-auto@tablet lrv-u-flex lrv-u-align-items-center u-height-20',
	header_sticky_menu_classes: 'lrv-u-flex lrv-u-align-items-center u-margin-r-150@mobile-max',
	header_sticky_secondary_classes: 'lrv-u-flex lrv-u-align-items-center lrv-u-justify-content-space-between lrv-u-padding-tb-025 u-padding-lr-075 a-hidden@tablet u-background-color-pale-sky',
	header_sticky_search_classes: 'lrv-u-flex lrv-u-align-items-center a-hidden@mobile-max u-margin-r-150',
	header_login_wrapper_classes: 'lrv-u-flex lrv-u-align-items-center',
	header_vip_navbar: header_vip_navbar,
	o_icon_button_link,
	o_nav: null,
	login_actions_mobile: null,
	is_vip_header: false,
	login_actions,
};
