const clonedeep = require( 'lodash.clonedeep' );

const o_nav_prototype = require( '@penskemediacorp/larva-patterns/objects/o-nav/o-nav.horizontal.js' );
const o_nav = clonedeep( o_nav_prototype );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );

const o_drop_menu_prototype = require( '../../objects/o-drop-menu/o-drop-menu.prototype.js' );
const o_drop_menu = clonedeep( o_drop_menu_prototype );

const nav_items_desktop = [ 'Popular on Variety', 'Emmy Awards', 'Red Carpet', 'Artisans', 'Academy Awards' ];
const drop_nav_items = [ 'Academy Awards', 'Actors on Actors: Film', 'Actors on Actors: TV', 'Artisans', 'Variety Studio at Cannes', 'Marvel Cinematic Universe', 'Power of Women', 'Red Carpet', 'Star Wars', 'Sundance', 'The Contenders', 'Emmys', 'Toronto Film Festival', 'Trailers' ];

o_nav.o_nav_list_items = [];
o_drop_menu.o_nav.o_nav_list_items = [];

for ( item of nav_items_desktop ) {
	let c_link = clonedeep( c_link_prototype );

	c_link.c_link_text = item;
	c_link.c_link_classes = 'lrv-u-color-white lrv-u-text-transform-uppercase u-font-family-basic u-font-size-15 u-letter-spacing-002';

	o_nav.o_nav_list_items.push( c_link );
}

o_nav.o_nav_list_classes += ' lrv-u-display-flex lrv-u-justify-content-space-between u-max-width-380@tablet';

for ( item of drop_nav_items ) {
	let c_link = clonedeep( c_link_prototype );

	c_link.c_link_text = item;
	c_link.c_link_url = '#page';
	c_link.c_link_classes = 'lrv-u-font-family-secondary lrv-u-text-transform-uppercase u-color-picked-bluewood u-color-picked-bluewood:hover	lrv-u-background-color-brand-primary:hover u-font-size-15 u-letter-spacing-007 lrv-u-display-block lrv-u-height-100p';

	o_drop_menu.o_nav.o_nav_list_items.push( c_link );
}

o_drop_menu.o_drop_menu_list_classes += ' lrv-u-background-color-white lrv-u-text-align-right u-box-shadow-menu@tablet lrv-u-width-300 u-padding-lr-075 lrv-u-padding-t-050 u-padding-b-150 lrv-a-glue@tablet lrv-a-glue--r-0';
o_drop_menu.o_nav.o_nav_list_classes += ' lrv-u-padding-t-050';
o_drop_menu.o_nav.o_nav_list_item_classes = 'u-padding-b-075';
o_drop_menu.c_span.c_span_text = 'More';
o_drop_menu.c_span.c_span_classes = 'lrv-u-color-white lrv-u-text-transform-uppercase u-font-family-basic u-font-size-15 u-letter-spacing-002 u-color-brand-primary:hover lrv-u-display-inline-flex lrv-u-text-align-right';

module.exports = {
	video_menu_classes: 'a-hidden@mobile-max lrv-u-flex u-background-color-picked-bluewood u-padding-b-075',
	video_menu_inner_classes: 'lrv-u-width-100p lrv-u-flex lrv-u-justify-content-space-between',
	o_nav,
	o_drop_menu,
};
