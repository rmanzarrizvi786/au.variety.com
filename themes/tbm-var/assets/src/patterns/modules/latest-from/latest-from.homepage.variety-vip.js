const clonedeep = require( 'lodash.clonedeep' );

const latest_from_prototype = require( './latest-from.prototype.js' );
const latest_from = clonedeep( latest_from_prototype );

const o_tease_prototype = require( '../../objects/o-tease/o-tease.latest-from.js' );
const o_tease = clonedeep( o_tease_prototype );

const o_tease_last = clonedeep( o_tease_prototype );
o_tease_last.o_tease_classes = 'lrv-u-flex lrv-u-flex-direction-column@tablet lrv-u-padding-b-1 u-padding-t-075 u-padding-t-00@tablet u-padding-b-00@tablet';

latest_from.latest_from_classes = 'lrv-u-margin-t-050 u-border-t-6@mobile-max u-border-color-brand-secondary-50 a-span3@tablet u-margin-t-175@tablet';
latest_from.latest_from_inner_classes += ' u-border-r-1@tablet u-border-color-brand-secondary-50';
latest_from.o_more_from_heading.o_more_from_heading_classes = latest_from.o_more_from_heading.o_more_from_heading_classes.replace( 'lrv-u-padding-tb-050', 'lrv-u-padding-b-050' );
latest_from.o_more_from_heading.o_more_from_heading_classes += ' lrv-u-padding-t-025 a-hidden@tablet';
latest_from.latest_from_primary_classes = latest_from.latest_from_primary_classes.replace( 'u-padding-lr-075', '' );
latest_from.o_tease_list.o_tease_list_classes = latest_from.o_tease_list.o_tease_list_classes.replace( 'u-padding-lr-175', 'lrv-u-padding-lr-1' );
latest_from.o_tease_list.o_tease_list_classes += ' lrv-u-height-100p';
latest_from.o_tease_list.o_tease_list_item_classes = latest_from.o_tease_list.o_tease_list_item_classes.replace( 'u-padding-r-175@tablet', '' );
latest_from.o_tease_list.o_tease_list_items = [
	o_tease,
	o_tease_last
];

module.exports = {
	...latest_from,
};
