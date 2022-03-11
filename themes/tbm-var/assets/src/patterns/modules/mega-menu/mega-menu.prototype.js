const clonedeep = require( 'lodash.clonedeep' );
const c_logo_prototype = require( '../../components/c-logo/c-logo.prototype' );
const c_logo = clonedeep( c_logo_prototype );

c_logo.c_logo_classes = "lrv-u-display-block u-width-110 lrv-u-color-white u-height-30 u-color-brand-primary-40:hover";

const expandable_search_prototype = require( '../expandable-search/expandable-search.prototype' );
const expandable_search_mega_menu = clonedeep( expandable_search_prototype );

const mega_menu_footer_prototype = require( '../mega-menu-footer/mega-menu-footer.prototype.js' );
const mega_menu_footer = clonedeep( mega_menu_footer_prototype );

const mega_menu_content_prototype = require( '../mega-menu-content/mega-menu-content.prototype.js' );
const mega_menu_content = clonedeep( mega_menu_content_prototype );

const search_form_prototype = require( '../search-form/search-form.prototype' );
const search_form_mobile = require( '../search-form/search-form.mobile.js' );
const search_form = clonedeep( search_form_prototype );

const o_nav_prototype = require( '../../objects/o-nav/o-nav.prototype.js' );
const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const mobile_navigation = clonedeep( o_nav_prototype );
const mobile_navigation_items = [ 'Have a news tip?', 'Subscribe', 'Newsletters' ];

const region_selector_mobile_prototype = require( '../region-selector/region-selector.mobile' );
const region_selector_mobile = clonedeep( region_selector_mobile_prototype );

mobile_navigation.o_nav_list_items = [];

for ( item of mobile_navigation_items ) {
	let c_link = clonedeep( c_link_prototype );

	c_link.c_link_text = item;
	c_link.c_link_classes = 'lrv-u-color-black lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-font-size-13 u-letter-spacing-2';

	mobile_navigation.o_nav_list_items.push( c_link );
}

mobile_navigation.o_nav_list_items[0].c_link_url = '/tips/';
mobile_navigation.o_nav_list_items[1].c_link_url = '/subscribe-us/';
mobile_navigation.o_nav_list_items[2].c_link_url = '#newslettersUrl';

search_form.search_form_classes = 'lrv-u-flex lrv-u-padding-t-1 u-min-height-50';
search_form.search_form_input_label_classes = 'lrv-u-width-100p u-margin-r-175 u-color-brand-secondary-30 lrv-a-icon-before lrv-a-icon-invert a-icon-search';
search_form.search_form_input_classes = 'a-reset-input a-reset-input--search lrv-u-border-a-0 u-border-b-1@tablet u-border-r-1@tablet u-border-color-pale-sky-2 lrv-u-font-family-secondary lrv-u-font-size-16 u-background-color-brand-accent-100-b u-color-brand-secondary-30 u-width-445 lrv-u-height-100p lrv-u-padding-l-2 ';
search_form.search_form_submit_classes = 'a-reset-input lrv-u-border-a-0 lrv-u-color-white lrv-u-font-family-secondary lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-background-color-pale-sky-2 u-letter-spacing-2 u-color-brand-primary-40:hover@tablet lrv-u-cursor-pointer';
search_form.search_form_input_placeholder_attr = 'Search People and Companies';

mobile_navigation.o_nav_classes = 'a-hidden@tablet u-margin-t-075 u-padding-lr-3';
mobile_navigation.o_nav_list_item_classes = 'lrv-u-margin-b-050';

module.exports = {
	c_logo: c_logo,
	mega_menu_footer: mega_menu_footer,
	mega_menu_content: mega_menu_content,
	expandable_search_mega_menu: expandable_search_mega_menu,
	search_form_mobile,
	mobile_navigation,
	region_selector_mobile,
	search_form,
};
