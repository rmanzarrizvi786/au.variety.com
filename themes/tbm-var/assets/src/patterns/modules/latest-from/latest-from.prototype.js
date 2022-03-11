const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.variety-vip.js' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const o_tease_primary_prototype = require( '../../objects/o-tease/o-tease.latest-from.primary.js' );
const o_tease_primary = clonedeep( o_tease_primary_prototype );

// Todo: replace for prototype when moving to variety-vip
const o_tease_list_prototype = require( '../../objects/o-tease-list/o-tease-list.homepage.variety-vip.js' );
const o_tease_list = clonedeep( o_tease_list_prototype );

const o_tease_prototype = require( '../../objects/o-tease/o-tease.latest-from.js' );
const o_tease = clonedeep( o_tease_prototype );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.prototype.js' );
const o_more_link = clonedeep( o_more_link_prototype );

o_more_from_heading.c_heading.c_heading_text = 'Latest From';
o_more_from_heading.c_heading.c_heading_classes += ' u-letter-spacing-025@mobile-max u-font-family-secondary@tablet';

o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'a-hidden@tablet', '' );
o_tease_list.o_tease_list_classes += ' u-padding-l-00@tablet lrv-a-grid u-grid-gap-0 u-grid-gap-150@tablet u-padding-r-125@tablet';
o_tease_list.o_tease_list_item_classes += ' u-border-t-1@mobile-max lrv-u-padding-b-025 lrv-u-height-100p u-padding-r-175@tablet';

o_tease_list.o_tease_list_items = [
	o_tease,
	o_tease,
];

o_more_link.c_link.c_link_text = 'Latest from VIP';
o_more_link.o_more_link_classes = 'lrv-u-border-b-1 lrv-u-border-t-1 lrv-u-padding-b-1 lrv-u-padding-r-050 lrv-u-text-align-right a-hidden@tablet u-border-color-loblolly-grey u-margin-lr-150 u-padding-t-075';

module.exports = {
	latest_from_classes: '',
	latest_from_inner_classes: 'lrv-a-grid lrv-a-cols3@tablet u-grid-gap-0@mobile-max u-grid-gap-137@tablet',
	latest_from_primary_classes: 'u-margin-t-025 u-padding-lr-075 u-border-r-1@tablet u-border-color-brand-secondary-50 a-span2@tablet u-padding-r-125@tablet u-margin-t-00@tablet',
	o_more_from_heading,
	o_tease_primary,
	o_tease_list,
	o_more_link,
};
