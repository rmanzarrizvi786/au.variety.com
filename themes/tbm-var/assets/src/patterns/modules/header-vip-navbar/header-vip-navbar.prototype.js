const clonedeep = require( 'lodash.clonedeep' );

const o_nav_prototype = require( '@penskemediacorp/larva-patterns/objects/o-nav/o-nav.horizontal.js' );
const o_nav = clonedeep( o_nav_prototype );
const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );

const nav_items = [ 'Daily Commentary', 'Special Reports', 'Videos' ];

o_nav.o_nav_list_items = [];
o_nav.o_nav_classes += ' lrv-u-align-items-center lrv-u-height-auto lrv-u-padding-tb-075@desktop u-height-40@tablet u-height-auto@mobile-max';
o_nav.o_nav_list_item_classes  += ' lrv-u-text-align-center@mobile-max lrv-u-padding-a-050@mobile-max lrv-u-padding-lr-2 lrv-u-line-height-small lrv-u-border-r-1';
o_nav.o_nav_list_classes = 'lrv-a-unstyle-list lrv-u-width-100p@mobile-max lrv-u-flex lrv-a-space-children-horizontal lrv-u-align-items-center lrv-u-a-unstyle-list ';

for ( item of nav_items ) {
	let c_link = clonedeep( c_link_prototype );

	c_link.c_link_text = item;
	c_link.c_link_classes = 'lrv-a-unstyle-link a-font-basic-m a-font-basic-s@mobile lrv-u-padding-tb-050@tablet lrv-u-padding-lr-025@mobile lrv-u-padding-lr-050@tablet u-letter-spacing-001@tablet lrv-u-color-grey-light u-letter-spacing-004 u-letter-spacing-001@mobile-max lrv-u-font-size-12 u-font-size-11@mobile-max';

	o_nav.o_nav_list_items.push( c_link );
}

module.exports = {
	navbar_menu_classes: 'u-background-color-brand-accent-100-b u-border-color-brand-secondary-30 lrv-u-color-white lrv-u-text-transform-uppercase a-header-vip-navbar',
	o_nav,
};