const clonedeep = require( 'lodash.clonedeep' );

const homepage_voices_prototype = require( './homepage-voices.prototype' );
const homepage_voices = clonedeep( homepage_voices_prototype );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' );
const c_span_title = clonedeep( c_span_prototype );
const c_span_subtitle = clonedeep( c_span_prototype );

const o_tease_primary_prototype = require( '../../objects/o-tease/o-tease.special.secondary' );
const o_tease_primary = clonedeep( o_tease_primary_prototype );

const o_tease_secondary_prototype = require( '../../objects/o-tease/o-tease.special.tertiary' );
const o_tease_secondary = clonedeep( o_tease_secondary_prototype );

const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' );
const c_lazy_image_mobile = clonedeep( c_lazy_image_prototype );

const {
	o_more_from_heading,
	o_tease_list,
	o_more_link,
} = homepage_voices;

c_span_title.c_span_text = 'Special Report';
c_span_title.c_span_classes = 'u-font-family-basic lrv-u-font-size-12 u-font-size-15@tablet lrv-u-color-white lrv-u-text-transform-uppercase lrv-u-display-block lrv-u-text-align-center@mobile-max u-margin-t-075 u-line-height-1 u-color-brand-secondary-40@tablet lrv-u-margin-t-1@tablet u-letter-spacing-001@tablet u-margin-t-1@desktop-xl';

o_more_from_heading.c_heading.c_heading_text = 'Addiction and Recovery';
o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'u-padding-t-075', '' );
o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'lrv-u-padding-t-050@tablet', 'u-padding-t-025@tablet' );
o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'u-margin-b-150@tablet', 'u-margin-b-075@tablet' );
o_more_from_heading.o_more_from_heading_classes += ' u-border-b-1@mobile-max u-border-color-pale-sky-2 u-padding-b-025 u-padding-t-00@desktop-xl';
o_more_from_heading.c_heading.c_heading_classes = 'lrv-u-color-white lrv-u-font-family-secondary u-font-size-25 lrv-u-font-weight-bold lrv-u-text-align-center u-letter-spacing-001 u-font-size-32@tablet';

c_span_subtitle.c_span_classes = 'a-hidden@tablet lrv-u-color-white lrv-u-font-family-primary u-font-size-15 lrv-u-display-block lrv-u-text-align-center u-padding-lr-150 u-margin-b-075';
c_span_subtitle.c_span_text = 'Insights on navigating a sober life in Hollywood';

o_more_link.c_link.c_link_text = 'More Stories';

c_lazy_image_mobile.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/351x459';
c_lazy_image_mobile.c_lazy_image_classes = 'a-hidden@tablet';
c_lazy_image_mobile.c_lazy_image_crop_class = 'a-crop-35x46';

o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'a-separator-spacing--r-1@tablet', '' );
o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'u-padding-b-1@tablet', 'u-padding-b-050@tablet' );
o_tease_list.o_tease_list_classes += ' u-margin-lr-n1@tablet';

o_tease_list.o_tease_list_items = [
	o_tease_primary,
	o_tease_secondary,
	o_tease_secondary,
	o_tease_secondary,
];

module.exports = {
	...homepage_voices,
	c_span_title,
	c_span_subtitle,
	c_lazy_image_mobile,
};
