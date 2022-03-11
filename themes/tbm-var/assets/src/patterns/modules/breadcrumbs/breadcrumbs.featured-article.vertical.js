const clonedeep = require( 'lodash.clonedeep' );
const breadcrumbs = clonedeep( require( './breadcrumbs.featured-article' ) );

breadcrumbs.o_nav.o_nav_list_items.map( ( c_link, i ) => {
	c_link.c_link_classes += ' u-color-white@tablet lrv-u-color-grey-light:hover';
} );

breadcrumbs.o_nav.o_nav_classes += ' a-icon-invert@tablet';

module.exports = breadcrumbs;
