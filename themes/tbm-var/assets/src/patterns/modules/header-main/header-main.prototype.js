const clonedeep = require( 'lodash.clonedeep' );

const expandable_search_prototype = require( '../expandable-search/expandable-search.prototype' );
const expandable_search = clonedeep( expandable_search_prototype );

const c_logo_prototype = require( '../../components/c-logo/c-logo.prototype' );
const c_logo = clonedeep( c_logo_prototype );

c_logo.c_logo_classes = "lrv-u-color-white u-width-76 u-width-205@tablet lrv-u-color-white:hover lrv-u-flex lrv-u-flex-direction-column lrv-u-justify-content-center";

const c_icon = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype' ) );

c_icon.c_icon_classes = 'lrv-u-display-block lrv-u-width-30 lrv-u-color-white lrv-u-margin-r-1';
c_icon.c_icon_name = '115-logo';

const o_icon_button_prototype = require( '@penskemediacorp/larva-patterns/objects/o-icon-button/o-icon-button.prototype' );
const o_icon_button_search_prototype = require( '@penskemediacorp/larva-patterns/objects/o-icon-button/o-icon-button.search' );

const o_icon_button_search = clonedeep( o_icon_button_search_prototype );
const o_icon_button_menu = clonedeep( o_icon_button_prototype );

// Menu Icon Button
o_icon_button_menu.c_icon.c_icon_name = 'hamburger-menu';
o_icon_button_menu.c_icon.c_icon_classes = 'lrv-u-display-block u-width-18 u-height-18 u-width-24@tablet u-height-24@tablet';
o_icon_button_menu.o_icon_button_classes = 'js-MegaMenu-Trigger lrv-u-align-items-center lrv-u-border-a-0 lrv-u-flex u-min-height-40 u-padding-r-075 a-become-close-button a-become-close-button--trigger lrv-u-background-color-transparent u-color-brand-secondary-30 u-color-brand-accent-20:hover';
o_icon_button_menu.c_span = null;
o_icon_button_menu.o_icon_button_screen_reader_text = 'Click to expand the Mega Menu';

/* Login Actions */
const login_actions = clonedeep( require( '../login-actions/login-actions.prototype' ) );
const login_actions_mobile = clonedeep( require( '../login-actions-mobile/login-actions-mobile.prototype' ) );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const c_link = clonedeep( c_link_prototype );

c_link.c_link_text = 'Introducing Variety VIP+';
c_link.c_link_classes = 'lrv-u-padding-t-050 lrv-u-padding-b-050 lrv-u-color-white lrv-u-font-family-secondary lrv-u-font-size-10';

const o_top_nav = clonedeep( require( '@penskemediacorp/larva-patterns/objects/o-nav/o-nav.horizontal.js' ) );

o_top_nav.o_nav_list_items = o_top_nav.o_nav_list_items.slice( 0, -1 );
o_top_nav.o_nav_list_classes = 'lrv-u-flex lrv-u-flex-direction-column lrv-a-unstyle-list';
o_top_nav.o_nav_list_items.forEach( item => {
	item.c_link_classes = 'lrv-u-color-white lrv-u-font-family-secondary lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-letter-spacing-2 u-color-brand-accent-20:hover lrv-u-display-block lrv-u-padding-lr-050 lrv-u-line-height-large';
} );
o_top_nav.o_nav_list_items[0].c_link_text = 'Have a News Tip?';
o_top_nav.o_nav_list_items[0].c_link_url = '/tips';
o_top_nav.o_nav_list_items[1].c_link_text = 'Newsletters';
o_top_nav.o_nav_list_items[1].c_link_url = '/signup';

const region_selector_prototype = require( '../region-selector/region-selector.prototype' );
const region_selector = clonedeep( region_selector_prototype );

module.exports = {
	header_sticky_classes: 'u-background-color-brand-accent-100-b lrv-u-width-100p',
	header_sticky_logo_classes: 'lrv-u-flex-shrink-0 u-margin-lr-auto@desktop lrv-u-flex lrv-u-align-items-center u-height-20 u-max-width-180 u-max-width-205@tablet u-height-60@tablet u-margin-l-n60@tablet',
	header_inner_classes: 'lrv-u-flex lrv-u-align-items-center lrv-u-justify-content-space-between u-height-90@tablet',
	expandable_search_wrapper_classes: 'lrv-u-flex lrv-u-align-items-center a-hidden@mobile-max lrv-u-margin-r-1',
	header_menu_icons_classes: 'lrv-u-flex',
	header_navigation_classes: 'a-hidden@mobile-max',
	header_login_classes: 'lrv-u-margin-l-1 lrv-u-flex lrv-u-align-items-center',
	o_icon_button_menu: o_icon_button_menu,
	o_icon_button_search: o_icon_button_search,
	header_main_show_special_icon: true,
	header_main_special_icon_classes: 'u-margin-l-n60@tablet',
	c_logo,
	c_icon,
	expandable_search,
	c_link,
	o_top_nav,
	region_selector,
	login_actions,
	login_actions_mobile
};
