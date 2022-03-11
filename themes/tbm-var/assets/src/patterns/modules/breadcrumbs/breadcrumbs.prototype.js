const clonedeep = require( 'lodash.clonedeep' );
const breadcrumbs = clonedeep( require( '@penskemediacorp/larva-patterns/modules/breadcrumbs/breadcrumbs.prototype' ) );

const menuLinks = [ 'Home', 'Retrospective' ];
const c_link_struct = clonedeep( breadcrumbs.o_nav.o_nav_list_items[0] );

breadcrumbs.o_nav.o_nav_list_items = [];

c_link_struct.c_link_classes += ' lrv-u-color-grey-dark:hover lrv-a-hover-effect lrv-u-whitespace-nowrap lrv-u-padding-lr-050 u-background-brand-primary-top-half-hover';

for (let i = 0; i < menuLinks.length; i++) {
	let c_link = clonedeep( c_link_struct );

	c_link.c_link_text = menuLinks[i];
	breadcrumbs.o_nav.o_nav_list_items.push( c_link );
}

breadcrumbs.o_nav.o_nav_classes = 'lrv-u-font-family-secondary lrv-u-text-transform-uppercase u-font-size-13 u-font-size-15@tablet u-padding-lr-050';
breadcrumbs.o_nav.o_nav_list_classes += ' lrv-u-flex a-children-icon-spacing-0 lrv-u-align-items-center a-children-icon';
breadcrumbs.o_nav.o_nav_list_classes = breadcrumbs.o_nav.o_nav_list_classes.replace( 'lrv-a-space-children--1', '' );

breadcrumbs.breadcrumbs_classes = 'a-children-icon-r-angle lrv-u-display-table u-colors-map-accent-70 u-letter-spacing-009 lrv-u-line-height-normal lrv-u-color-brand-primary u-background-brand-primary-bottom-half lrv-u-padding-tb-025';

module.exports = breadcrumbs;
