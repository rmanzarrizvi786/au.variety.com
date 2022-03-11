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

const c_dek_prototype = require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' );
const c_dek = clonedeep( c_dek_prototype );

c_dek.c_dek_classes = 'lrv-u-font-family-secondary lrv-u-margin-b-00 lrv-u-margin-t-025 lrv-a-font-secondary-regular-m u-line-height-125 lrv-u-font-size-14@mobile-max';

c_dek.c_dek_text = 'This is what the news should sound like. The biggest stories of our time, told by the best journalists in the world. Hosted by Michael Barbaro. Twenty minutes a day, five days a week, ready by 6 a.m.';

o_tease_primary.c_dek = c_dek;

o_tease_primary.o_tease_classes = o_tease_primary.o_tease_classes.replace( 'lrv-u-border-b-1', '' );
o_tease_primary.o_tease_classes = o_tease_primary.o_tease_classes.replace( 'lrv-u-padding-b-1  ', '' );
o_tease_primary.o_tease_classes += ' lrv-u-padding-b-2@mobile-max lrv-u-margin-lr-050@mobile-max u-border-b-1@mobile-max ';

o_tease_primary.c_title.c_title_classes = o_tease_primary.c_title.c_title_classes.replace( 'u-font-size-22', 'lrv-u-font-size-24' );
o_tease_primary.c_title.c_title_classes = o_tease_primary.c_title.c_title_classes.replace( 'u-min-height-55', '' );

const o_tease_secondary_prototype = require( '../../objects/o-tease/o-tease.latest-from' );
const o_tease_secondary = clonedeep( o_tease_secondary_prototype );

o_tease_secondary.o_tease_classes = o_tease_secondary.o_tease_classes.replace( 'lrv-u-align-items-center', '' );
o_tease_secondary.o_tease_classes = o_tease_secondary.o_tease_classes.replace( 'lrv-u-padding-b-1', 'u-padding-b-125@tablet' );
o_tease_secondary.o_tease_classes = o_tease_secondary.o_tease_classes.replace( 'u-padding-t-075', 'u-padding-t-075@tablet' );
o_tease_secondary.o_tease_classes += ' lrv-u-margin-lr-050@mobile-max lrv-u-padding-lr-00@mobile-max lrv-u-padding-tb-1@mobile-max u-border-b-1@mobile-max u-border-color-brand-secondary-40 ';

o_tease_secondary.c_title.c_title_classes = o_tease_secondary.c_title.c_title_classes.replace( 'u-font-size-15', 'u-font-size-15@tablet' );
o_tease_secondary.c_title.c_title_classes = o_tease_secondary.c_title.c_title_classes.replace( 'font-size-16@tablet', 'font-size-16@desktop-xl' );
o_tease_secondary.c_title.c_title_classes = o_tease_secondary.c_title.c_title_classes.replace( 'u-line-height-120', 'u-line-height-125' );
o_tease_secondary.c_title.c_title_classes += ' u-margin-t-050@tablet lrv-u-font-size-14@mobile-max ';

o_tease_secondary.c_span.c_span_classes = 'lrv-u-display-none';
o_tease_secondary.c_link.c_link_classes = 'lrv-u-display-none';
o_tease_secondary.c_timestamp.c_timestamp_classes = 'lrv-u-display-none';

o_tease_secondary.o_tease_primary_classes += ' lrv-u-padding-l-050@mobile-max ';

o_tease_secondary.o_tease_secondary_classes = o_tease_secondary.o_tease_secondary_classes.replace( 'u-order-n1@tablet', 'u-order-n1' );

c_v_icon.c_icon_classes = 'u-width-75 u-height-25 u-width-100@tablet u-height-30@tablet u-margin-t-075@mobile-max u-margin-b-075@mobile-max u-margin-b-050 u-margin-t-075@mobile-max u-margin-t-1@tablet';

o_more_from_heading.o_more_from_heading_classes += ' lrv-u-padding-l-1 ';

o_more_from_heading.c_v_icon = null;

o_more_link.c_link.c_link_text = 'More Vip';
o_more_from_heading.c_heading.c_heading_text = 'VIP+';
o_more_link.o_more_link_classes = o_more_link.o_more_link_classes.replace( 'lrv-u-border-t-1', 'u-border-t-1@tablet' );
o_more_link.o_more_link_classes = o_more_link.o_more_link_classes.replace( 'lrv-u-padding-tb-075', 'lrv-u-padding-tb-1' );
o_more_link.o_more_link_classes += ' lrv-u-margin-lr-1 lrv-u-margin-lr-050@mobile-max ';

o_tease_list.o_tease_list_classes += ' lrv-u-flex@tablet lrv-u-justify-content-space-between lrv-u-flex-grow-1 u-margin-b-075@tablet u-border-b-1@tablet u-border-color-brand-secondary-40 lrv-u-padding-b-1@tablet ';
o_tease_list.o_tease_list_item_classes = 'u-width-50p@tablet u-margin-t-0@tablet';


const o_tease_secondary_first = clonedeep( o_tease_secondary );
const o_tease_secondary_second = clonedeep( o_tease_secondary );
const o_tease_secondary_third = clonedeep( o_tease_secondary );

const o_tease_list_bottom = clonedeep( o_tease_list );

o_tease_list_bottom.o_tease_list_classes = o_tease_list_bottom.o_tease_list_classes.replace( 'u-border-b-1@tablet', '' );

o_tease_secondary_first.o_tease_classes += ' lrv-u-padding-r-050 ';
o_tease_secondary_third.o_tease_classes += ' lrv-u-padding-r-050 ';

o_tease_secondary_second.o_tease_classes += ' lrv-u-padding-l-050 ';
o_tease_secondary.o_tease_classes += ' lrv-u-padding-l-050 ';

o_tease_list.o_tease_list_items = [
	o_tease_secondary_first,
	o_tease_secondary_second,
];

o_tease_list_bottom.o_tease_list_items = [
	o_tease_secondary_third,
	o_tease_secondary,
];

module.exports = {
	homepage_horizontal_classes: 'u-border-t-6 lrv-u-background-color-white u-box-shadow-menu',
	homepage_horizontal_inner_classes: 'lrv-a-grid lrv-a-cols4@tablet u-grid-gap-0@mobile-max u-grid-gap-0@tablet',
	homepage_horizontal_primary_classes: 'lrv-u-padding-lr-1@tablet u-border-r-1@tablet u-border-color-brand-secondary-40 a-span2@tablet u-margin-t-00@tablet lrv-u-margin-b-1 u-margin-b-00\@mobile-max',
	homepage_horizontal_secondary_classes: 'lrv-u-padding-lr-1@tablet c-1@tablet a-span2@tablet u-margin-t-00@tablet',
	o_more_from_heading,
	o_tease_primary,
	o_tease_list,
	o_tease_list_bottom,
	o_more_link,
};
