const clonedeep = require( 'lodash.clonedeep' );
const breadcrumbs = clonedeep( require( '@penskemediacorp/larva-patterns/modules/breadcrumbs/breadcrumbs.prototype' ) );
const menuLinks = [ 'Cover Story', 'Film', 'Actors' ];

breadcrumbs.o_nav.o_nav_classes = '';

breadcrumbs.o_nav.o_nav_list_items.map( ( c_link, i ) => {
  c_link.c_link_classes += ' lrv-u-whitespace-nowrap u-text-decoration-underline';
  c_link.c_link_text = menuLinks[i];
});

breadcrumbs.o_nav.o_nav_list_classes += ' lrv-u-flex a-children-icon-spacing-1 lrv-u-align-items-center a-children-icon a-children-icon-r-angle u-letter-spacing-009 lrv-u-line-height-normal lrv-u-padding-b-025   lrv-u-padding-t-1 lrv-u-margin-b-050 lrv-u-color-black u-font-family-basic lrv-u-justify-content-center lrv-u-flex-wrap-wrap lrv-u-font-size-12 lrv-u-font-size-14@tablet';

module.exports = breadcrumbs;
