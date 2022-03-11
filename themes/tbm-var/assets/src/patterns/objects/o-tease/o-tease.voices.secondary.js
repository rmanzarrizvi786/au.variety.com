const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.popular' );
const o_tease = clonedeep( o_tease_prototype );

o_tease.o_tease_classes += ' lrv-u-padding-b-050 u-flex-direction-column@tablet';
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-width-65', 'lrv-u-width-100' );
o_tease.o_tease_secondary_classes += ' u-padding-t-00@tablet u-padding-b-00@tablet u-width-100p@tablet';
o_tease.c_span.c_span_text = 'Marc Malkin';
o_tease.c_span.c_span_classes = 'lrv-u-text-transform-uppercase u-font-family-accent lrv-u-font-size-24 u-letter-spacing-001 u-font-size-25@tablet u-border-b-1@tablet u-border-color-pale-sky-2 u-display-inline-flex lrv-u-margin-b-1@tablet';
o_tease.c_span.c_span_link_classes = o_tease.c_span.c_span_link_classes.replace( 'u-color-pale-sky-2', 'lrv-u-color-white' );
o_tease.c_span.c_span_link_classes = o_tease.c_span.c_span_link_classes.replace( 'u-color-black:hover', 'u-color-brand-accent-20:hover' );
o_tease.c_span.c_span_link_classes += ' u-padding-b-00@tablet';
o_tease.c_lazy_image.c_lazy_image_classes += ' u-max-width-175@tablet u-max-width-225@desktop-xl';
o_tease.c_lazy_image.c_lazy_image_crop_class += ' a-crop-89x59@tablet';
o_tease.c_title.c_title_classe = o_tease.c_title.c_title_classes.replace( 'lrv-u-font-weight-bold', 'lrv-u-font-weight-normal' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'lrv-u-font-weight-bold', 'lrv-u-font-weight-normal' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-size-13', 'u-font-size-15' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-weight-bold@tablet', 'u-font-weight-normal@tablet' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-max-height-36em', 'u-max-height-36em@mobile-max' );
o_tease.c_title.c_title_classes += ' u-font-size-21@tablet u-font-family-primary@tablet';
o_tease.c_title.c_title_link_classes = o_tease.c_title.c_title_link_classes.replace( 'lrv-u-color-black', 'lrv-u-color-white' );
o_tease.c_title.c_title_link_classes = o_tease.c_title.c_title_link_classes.replace( 'u-color-brand-secondary-50:hover', 'u-color-brand-accent-20:hover' );

module.exports = {
	...o_tease,
};
