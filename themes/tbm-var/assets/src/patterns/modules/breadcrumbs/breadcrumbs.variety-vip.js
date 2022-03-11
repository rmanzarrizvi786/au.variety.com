const clonedeep = require( 'lodash.clonedeep' );
const breadcrumbs = clonedeep( require( '@penskemediacorp/larva-patterns/modules/breadcrumbs/breadcrumbs.prototype' ) );

const menuLinks = [ 'VIP', 'Special Report', 'Corporate Focus' ];
const c_link_struct = clonedeep( breadcrumbs.o_nav.o_nav_list_items[0] );

breadcrumbs.o_nav.o_nav_list_items = [];

c_link_struct.c_link_classes += ' lrv-u-color-grey-dark:hover lrv-a-hover-effect lrv-u-font-weight-bold u-font-weight-normal@mobile-max lrv-u-whitespace-nowrap';

for (let i = 0; i < menuLinks.length; i++) {
	let c_link = clonedeep( c_link_struct );

	c_link.c_link_text = menuLinks[i];
	breadcrumbs.o_nav.o_nav_list_items.push( c_link );
}

breadcrumbs.o_nav.o_nav_classes = 'lrv-u-text-transform-uppercase lrv-u-font-family-secondary u-font-size-15 u-letter-spacing-012';
breadcrumbs.o_nav.o_nav_list_classes += ' lrv-u-flex lrv-u-flex-direction-column@mobile-max lrv-a-space-children-horizontal lrv-a-space-children--050 lrv-u-align-items-center a-children-icon-after a-children-icon-r-angle-quote a-children-icon-spacing-050';
breadcrumbs.breadcrumbs_classes = 'lrv-u-color-brand-primary lrv-u-text-align-center@mobile-max u-padding-lr-225@mobile-max';

module.exports = breadcrumbs;
