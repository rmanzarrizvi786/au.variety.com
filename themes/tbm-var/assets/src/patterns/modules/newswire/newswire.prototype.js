const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.homepage' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const o_tease_list_prototype = require( '../../objects/o-tease-list/o-tease-list.prototype' );
const o_tease_list = clonedeep( o_tease_list_prototype );

const o_tease_prototype = require( '../../objects/o-tease/o-tease.must-read-primary' );
const o_tease = clonedeep( o_tease_prototype );
o_tease.c_timestamp = false;
o_tease.c_link = false;

o_more_from_heading.c_heading.c_heading_text = 'More From Our Brands';
o_more_from_heading.c_heading.c_heading_classes += ' a-font-secondary-bold-3xl@tablet';

o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'lrv-u-border-b-1', '' );
o_tease.o_tease_classes += ' lrv-u-padding-lr-1@tablet';
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'lrv-u-font-weight-normal', '' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-size-16@tablet', '' );
o_tease.c_span.c_span_text = 'IndieWire';
o_tease.c_span.c_span_classes += ' u-margin-b-050@tablet';

o_tease.c_lazy_image.c_lazy_image_classes = o_tease.c_lazy_image.c_lazy_image_classes.replace( 'u-margin-lr-n075@mobile-max', '' );

o_tease_list.o_tease_list_classes += ' a-grid-first-child-span-all@mobile-max u-align-items-stretch lrv-a-grid a-cols2 a-cols5@tablet u-grid-gap-075 u-grid-gap-0@tablet a-separator-b-1@mobile-max a-separator-r-1@tablet u-margin-lr-n1@tablet';
o_tease_list.o_tease_list_item_classes = 'u-border-color-brand-secondary-40';

o_tease_list.o_tease_list_items = [
	o_tease,
	o_tease,
	o_tease,
	o_tease,
	o_tease
];

module.exports = {
	newswire_wrapper_classes: 'lrv-a-wrapper lrv-u-margin-tb-1',
	newswire_classes: 'lrv-u-background-color-white u-border-color-picked-bluewood u-border-t-6 u-box-shadow-menu u-padding-lr-075 u-padding-lr-1@tablet u-padding-b-125@tablet',
	newswire_header_classes: 'lrv-u-flex lrv-u-flex-direction-column@mobile-max u-align-items-flex-end@tablet',
	o_more_from_heading,
	o_tease_list
};
