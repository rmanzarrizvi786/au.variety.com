const clonedeep = require( 'lodash.clonedeep' );

const c_heading = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' ) );
const o_nav = clonedeep( require( '@penskemediacorp/larva-patterns/objects/o-nav/o-nav.prototype' ) );
const c_link = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' ) );

c_heading.c_heading_text = 'Film';
c_heading.c_heading_classes = 'lrv-u-font-family-primary lrv-u-font-weight-normal lrv-u-font-size-50 lrv-u-margin-b-050@mobile-max lrv-u-line-height-small lrv-u-text-align-center@mobile-max u-margin-r-2@tablet u-font-size-65@tablet u-font-size-70@desktop-xl u-color-white@tablet';
c_heading.c_heading_is_primary_heading = true;

o_nav.o_nav_list_items = [];
const menuLinks = ['Tag Title', 'Tag Title', 'Tag Title', 'Tag Title', 'Tag Title'];

for ( let i = 0; i < menuLinks.length; i++ ) {
	let new_c_link = clonedeep( c_link );

	new_c_link.c_link_text = menuLinks[i];
	new_c_link.c_link_classes += ' u-color-brand-primary:hover';

	o_nav.o_nav_list_items.push( new_c_link );
}

o_nav.o_nav_classes = 'lrv-js-MobileHeightToggle u-font-family-basic lrv-u-text-transform-uppercase lrv-u-font-size-12 lrv-u-font-size-14@tablet u-letter-spacing-001 lrv-u-color-grey-dark lrv-u-width-100p lrv-u-flex lrv-u-justify-content-center lrv-u-flex-direction-column u-color-white@tablet';
o_nav.o_nav_title_text = 'All';
o_nav.o_nav_title_classes = 'a-hidden@tablet lrv-js-MobileHeightToggle-trigger lrv-a-icon-after lrv-a-icon-arrow-down lrv-u-font-weight-normal lrv-u-padding-tb-050 lrv-u-width-100p lrv-u-justify-content-center lrv-u-margin-b-050@mobile-max u-background-white@mobile-max lrv-u-border-b-1 lrv-u-border-color-grey-light';
o_nav.o_nav_list_classes += ' lrv-js-MobileHeightToggle-target lrv-u-flex lrv-u-flex-direction-column@mobile-max lrv-a-space-children--1 lrv-a-space-children-horizontal@tablet lrv-u-justify-content-center';
o_nav.o_nav_list_item_classes = 'lrv-u-padding-a-050';

module.exports = {
	sub_header_classes: 'lrv-u-text-align-center u-background-color-picked-bluewood@tablet',
	c_heading: c_heading,
	o_nav: o_nav,
};
