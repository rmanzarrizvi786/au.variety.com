const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.homepage' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const o_nav_prototype = require( '@penskemediacorp/larva-patterns/objects/o-nav/o-nav.horizontal' );
const o_nav = clonedeep( o_nav_prototype );

const o_tease_list_prototype = require( '../../objects/o-tease-list/o-tease-list.prototype' );
const o_tease_first_prototype = require( '../../objects/o-tease/o-tease.reviews.primary' );
const o_tease_prototype = require( '../../objects/o-tease/o-tease.reviews' );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.blue' );
const o_more_link = clonedeep( o_more_link_prototype );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' );

const navItems = [ 'TV', 'Film', 'Music', 'Legit' ];
const reviews_lists = [];

const o_tease_first = clonedeep( o_tease_first_prototype );
const o_tease = clonedeep( o_tease_prototype );

o_more_from_heading.c_heading.c_heading_text = 'Reviews';
o_more_from_heading.c_heading.c_heading_classes += ' a-font-secondary-bold-3xl@tablet u-margin-r-250@tablet';

o_tease_first.o_tease_classes += ' lrv-u-padding-lr-1@tablet';

o_tease.o_tease_classes += ' lrv-u-padding-lr-1@tablet';

/* Example of a review variation with no critics pick label present */
const o_tease_no_critics_pick = clonedeep( o_tease );
o_tease_no_critics_pick.c_span = null;

o_nav.o_nav_list_items = [];
o_nav.o_nav_classes += ' lrv-u-justify-content-center';
o_nav.o_nav_list_classes += ' a-space-children--2@tablet';

o_more_link.o_more_link_classes += ' lrv-u-text-align-right lrv-u-padding-t-050 lrv-u-padding-b-050 u-border-t-1@tablet u-border-color-brand-secondary-40 u-margin-t-2@tablet u-margin-t-150@desktop-xl u-padding-tb-075@desktop-xl';
o_more_link.c_link.c_link_text = 'More Reviews';

for ( item of navItems ) {
	let c_link = clonedeep( c_link_prototype );

	c_link.c_link_text = item;
	c_link.c_link_url = `#${item.toLowerCase()}`;
	c_link.c_link_classes = 'js-TabsToggle lrv-u-text-transform-uppercase u-font-family-basic u-font-size-15 u-font-size-18@tablet u-font-weight-normal u-color-pale-sky-2 u-color-pale-sky-2:hover u-letter-spacing-001';

	o_nav.o_nav_list_items.push( c_link );

	let o_tease_list = clonedeep( o_tease_list_prototype );

	o_tease_list.o_tease_list_id_attr = item.toLowerCase();
	o_tease_list.o_tease_list_classes += ' js-TabsPanel lrv-a-grid u-grid-gap-0 a-cols4@tablet a-separator-r-1@tablet u-margin-lr-n1@tablet';
	o_tease_list.o_tease_list_item_classes = 'u-border-color-brand-secondary-40 lrv-u-height-100p';
	o_tease_list.o_tease_list_items = [
		o_tease_first,
		o_tease,
		o_tease_no_critics_pick,
		o_tease,
	];

	reviews_lists.push( o_tease_list );
}

o_nav.o_nav_list_items[0].c_link_classes += ' is-active';
reviews_lists[0].o_tease_list_classes += ' is-active';

module.exports = {
	reviews_outer_classes: 'lrv-a-wrapper lrv-u-margin-t-1',
	reviews_classes: 'js-Tabs u-border-t-6 u-border-color-picked-bluewood u-padding-lr-075@mobile-max lrv-u-padding-lr-1@tablet u-box-shadow-menu lrv-u-background-color-white',
	reviews_header_classes: 'lrv-u-flex lrv-u-flex-direction-column@mobile-max',
	reviews_inner_classes: 'u-margin-t-075@mobile-max',
	o_more_from_heading,
	o_nav,
	o_more_link,
	reviews_lists,
};
