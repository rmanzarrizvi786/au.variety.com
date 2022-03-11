const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.homepage' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const c_icon_prototype = require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype.js' );
const c_v_icon = clonedeep( c_icon_prototype );

const o_tease_list_prototype = require( '../../objects/o-tease-list/o-tease-list.prototype' );
const o_tease_list = clonedeep( o_tease_list_prototype );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.blue.homepage' );
const o_more_link = clonedeep( o_more_link_prototype );

const o_tease_primary_prototype = require( '../../objects/o-tease/o-tease.latest-from.vip' );
const o_tease_primary = clonedeep( o_tease_primary_prototype );

const o_tease_secondary_prototype = require( '../../objects/o-tease/o-tease.latest-from' );
const o_tease_secondary = clonedeep( o_tease_secondary_prototype );

o_tease_secondary.o_tease_classes = o_tease_secondary.o_tease_classes.replace( 'lrv-u-align-items-center', '' );
o_tease_secondary.o_tease_classes = o_tease_secondary.o_tease_classes.replace( 'lrv-u-padding-b-1', 'u-padding-b-125' );

o_tease_secondary.c_title.c_title_classes += ' u-margin-t-050@tablet';
o_tease_secondary.c_span.c_span_classes = 'lrv-u-display-none';
o_tease_secondary.c_link.c_link_classes = 'lrv-u-display-none';
o_tease_secondary.c_timestamp.c_timestamp_classes = 'lrv-u-display-none';

c_v_icon.c_icon_name = 'vip-plus';
// Note: u-margin-t-075@mobile-max u-margin-b-075@mobile-max u-margin-b-075 u-margin-t-075@mobile-max u-margin-t-125@tablet are to match
// lrv-u-padding-tb-075 in c-heading.accent-m since there is no text here.
// This should be part of a variant, not the prototype
c_v_icon.c_icon_classes = 'u-width-75 u-height-25 u-width-100@tablet u-height-30@tablet u-margin-t-075@mobile-max u-margin-b-075@mobile-max u-margin-b-050 u-margin-t-075@mobile-max u-margin-t-1@tablet';

o_more_from_heading.c_heading.c_heading_classes += ' lrv-a-screen-reader-only';
o_more_from_heading.c_v_icon = c_v_icon;

o_more_link.c_link.c_link_text = 'More Vip';

o_tease_list.o_tease_list_classes += ' lrv-u-flex@tablet lrv-u-justify-content-space-between a-separator-b-1@mobile-max a-separator-r-1@tablet a-separator-spacing--r-1@tablet lrv-u-flex-grow-1 u-margin-b-075@tablet';
o_tease_list.o_tease_list_item_classes = 'u-border-color-brand-secondary-40 u-width-100p@tablet u-margin-t-075@tablet';

const o_tease_secondary_first = clonedeep( o_tease_secondary );

o_tease_list.o_tease_list_items = [
	o_tease_secondary_first,
	o_tease_secondary,
];

module.exports = {
	vip_curated_classes: 'u-border-t-6 u-border-color-vip-brand-primary u-box-shadow-menu lrv-u-background-color-white lrv-u-height-100p lrv-u-flex@tablet lrv-u-flex-direction-column u-padding-lr-075@mobile-max lrv-u-padding-lr-1@tablet',
	vip_curated_stories_classes: 'u-margin-t-1@tablet lrv-u-flex-grow-1 lrv-u-flex@tablet lrv-u-flex-direction-column',
	o_more_from_heading,
	o_tease_primary,
	o_tease_list,
	o_more_link,
};
