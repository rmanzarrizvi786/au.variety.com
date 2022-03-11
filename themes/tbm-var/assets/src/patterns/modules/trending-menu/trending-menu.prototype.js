const clonedeep = require( 'lodash.clonedeep' );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' );
const c_span = clonedeep( c_span_prototype );

const o_nav_prototype = require( '@penskemediacorp/larva-patterns/objects/o-nav/o-nav.horizontal' );
const o_nav = clonedeep( o_nav_prototype );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' );

const menuItems = [ '‘Watchmen’', '‘Megyn Kelly’', '‘Succession’', '‘El Camino’', '‘Joker’', 'Quentin Tarantino' ];

c_span.c_span_text = 'Trending:';
c_span.c_span_classes = 'lrv-u-font-family-secondary lrv-u-font-size-14 lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-color-ebony-clay u-letter-spacing-2';

o_nav.o_nav_list_items = [];
o_nav.o_nav_list_classes += ' a-space-children--150@desktop-xl lrv-u-flex-wrap-wrap';

for ( item of menuItems ) {
	let c_link = clonedeep( c_link_prototype );

	c_link.c_link_text = item;
	c_link.c_link_url = '#category';
	c_link.c_link_classes = 'lrv-u-font-family-secondary lrv-u-font-size-14 lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-color-pale-sky-2 u-color-brand-secondary-80:hover u-letter-spacing-2';

	o_nav.o_nav_list_items.push( c_link );
}

module.exports = {
	trending_menu_classes: 'lrv-u-background-color-white lrv-u-padding-tb-050 u-box-shadow-menu@tablet a-truncate-ellipsis lrv-u-max-width-100p a-hidden@mobile-max',
	trending_menu_inner_classes: 'lrv-a-wrapper lrv-u-flex lrv-u-align-items-center lrv-u-justify-content-center lrv-a-space-children-horizontal a-space-children--150 lrv-u-flex-wrap-wrap',
	c_span,
	o_nav,
};
