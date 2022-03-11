const clonedeep = require( 'lodash.clonedeep' );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype.js' );
const c_span = clonedeep( c_span_prototype );

const o_nav_prototype = require( '@penskemediacorp/larva-patterns/objects/o-nav/o-nav.prototype.js' );
const o_nav = clonedeep( o_nav_prototype );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );

const drop_nav_items = [ 'Academy Awards', 'Actors on Actors: Film', 'Actors on Actors: TV', 'Artisans', 'Variety Studio at Cannes', 'Marvel Cinematic Universe', 'Power of Women', 'Red Carpet', 'Star Wars', 'Sundance', 'The Contenders', 'Emmys', 'Toronto Film Festival', 'Trailers' ];

o_nav.o_nav_list_items = [];

for ( item of drop_nav_items ) {
	let c_link = clonedeep( c_link_prototype );

	c_link.c_link_text = item;
	c_link.c_link_url = '#page';
	c_link.c_link_classes = 'lrv-u-font-family-secondary lrv-u-text-transform-uppercase u-color-picked-bluewood u-color-picked-bluewood:hover	lrv-u-background-color-brand-primary:hover u-font-size-15 u-letter-spacing-007 lrv-u-display-block lrv-u-height-100p';

	o_nav.o_nav_list_items.push( c_link );
}

c_span.c_span_text = 'Popular on Variety';
c_span.c_span_classes = 'lrv-u-color-white lrv-u-text-transform-uppercase lrv-a-icon-before lrv-a-icon-invert lrv-u-font-size-12 a-icon-down-caret-small a-icon-small u-font-family-basic u-letter-spacing-002';

o_nav.o_nav_list_classes += ' lrv-u-padding-t-050';
o_nav.o_nav_list_item_classes = 'u-padding-b-075';

module.exports = {
	video_menu_mobile_classes: 'a-hidden@tablet u-background-color-picked-bluewood',
	video_menu_trigger_classes: 'lrv-u-display-block lrv-u-text-align-center',
	video_menu_list_classes: 'lrv-u-background-color-white lrv-u-width-100p u-padding-lr-075 lrv-u-padding-t-050 u-padding-b-150',
	c_span,
	o_nav,
};
