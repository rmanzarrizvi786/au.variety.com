const clonedeep = require( 'lodash.clonedeep' );

const header_sticky_prototype = require( '../header-sticky/header-sticky.variety-vip.js' );
const header_sticky = clonedeep( header_sticky_prototype );

const o_nav_prototype = require( '@penskemediacorp/larva-patterns/objects/o-nav/o-nav.prototype.js' );
const o_nav_primary = clonedeep( o_nav_prototype );
const o_nav_secondary = clonedeep( o_nav_prototype );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );

const menuLinks = [ 'Special Reports', 'Daily Commentary', 'Archives', 'Back to Variety' ];
const secondaryLinks = [ 'Have a news Tip?', 'Subscribe' ];

const search_form_prototype = require( '../search-form/search-form.mobile.js' );
const search_form = clonedeep( search_form_prototype );

o_nav_primary.o_nav_list_items = [];
o_nav_secondary.o_nav_list_items = [];

o_nav_primary.o_nav_classes = 'lrv-u-margin-t-2';
o_nav_primary.o_nav_list_item_classes = 'lrv-u-flex lrv-u-border-b-1 u-border-color-iron-grey';

o_nav_secondary.o_nav_classes = 'u-margin-t-150';
o_nav_secondary.o_nav_list_item_classes = 'lrv-u-margin-b-050';

search_form.search_form_classes = search_form.search_form_classes.replace( 'u-padding-lr-3', '' );
search_form.search_form_input_classes = search_form.search_form_input_classes.replace( 'u-background-color-geyser', 'u-background-color-white' );
search_form.search_form_input_classes += ' u-border-color-iron-grey';

for ( item of menuLinks ) {
	let c_link = clonedeep( c_link_prototype );

	c_link.c_link_text = item;
	c_link.c_link_classes = 'lrv-u-color-black lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-18 u-padding-lr-250 lrv-u-padding-b-050 u-padding-t-125  u-padding-b-050@tablet';

	if ( 'Back to Variety' === item ) {
		c_link.c_link_classes += ' lrv-a-icon-before a-icon-left-caret u-margin-l-n150';
	}

	o_nav_primary.o_nav_list_items.push( c_link );
}

for ( item of secondaryLinks ) {
	let c_link = clonedeep( c_link_prototype );

	c_link.c_link_text = item;
	c_link.c_link_classes = 'lrv-u-color-black lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-font-size-13 u-letter-spacing-2';

	o_nav_secondary.o_nav_list_items.push( c_link );
}

o_nav_secondary.o_nav_list_items[0].c_link_url = '/tips/';
o_nav_secondary.o_nav_list_items[1].c_link_url = '/subscribe-us/';

module.exports = {
	...header_sticky,
	vip_menu_classes: 'u-padding-lr-3 lrv-a-glue lrv-a-glue--b-0 lrv-a-glue--l-0 lrv-a-glue--r-0 a-glue--t-250 a-glue--t-275@tablet lrv-u-background-color-white u-width-350@tablet u-height-350@tablet u-border-a-1@tablet u-border-color-dusty-grey a-glue--l-auto@tablet u-box-shadow-menu@tablet',
	header_sticky,
	o_nav_primary,
	o_nav_secondary,
	search_form,
};
