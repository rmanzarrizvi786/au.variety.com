const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.variety-vip.js' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const o_tease_list_prototype = require( '../../objects/o-tease-list/o-tease-list.prototype.js' );
const o_tease_list = clonedeep( o_tease_list_prototype );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.prototype.js' );
const o_more_link = clonedeep( o_more_link_prototype );

const o_more_link_previous_prototype = require( '../../objects/o-more-link/o-more-link.previous.js' );
const o_more_link_previous = clonedeep( o_more_link_previous_prototype );

const o_tease_prototype = require( '../../objects/o-tease/o-tease.prototype' );
const o_tease_list_first = clonedeep( o_tease_prototype );

o_more_from_heading.o_more_from_heading_classes = 'lrv-u-flex u-justify-content-center@mobile-max lrv-u-align-items-center lrv-u-text-align-center lrv-u-padding-t-025 lrv-u-padding-t-050@tablet lrv-u-padding-b-050 u-border-b-1@tablet u-border-color-loblolly-grey u-padding-b-1@tablet';
o_more_from_heading.c_heading.c_heading_classes = 'u-text-transform-uppercase@mobile-max lrv-u-font-family-primary u-font-size-30 u-font-weight-medium u-letter-spacing-040@mobile-max u-font-family-secondary@tablet u-font-size-32@tablet u-font-weight-bold@tablet';

o_tease_list.o_tease_list_classes += ' u-padding-lr-150@mobile-max';
o_tease_list.o_tease_list_item_classes = 'lrv-u-border-b-1 u-border-color-loblolly-grey';

o_tease_list.o_tease_list_items.unshift( o_tease_list_first );

o_tease_list.o_tease_list_items.forEach( item => item.c_title.c_title_classes += ' u-min-height-24em' );

o_more_link.o_more_link_classes = 'lrv-u-text-align-right u-margin-t-075 u-padding-lr-150@mobile-max';
o_more_link.c_link.c_link_text = 'More Stories';

o_more_link.o_more_link_classes = 'lrv-u-margin-l-auto';

module.exports = {
	more_from_widget_classes: 'u-border-t-6 u-border-color-brand-secondary-50 lrv-u-margin-b-2',
	o_more_from_heading,
	o_tease_list,
	o_more_link,
	o_more_link_previous
};
