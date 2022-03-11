const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.must-read-primary' );
const o_tease = clonedeep( o_tease_prototype );

o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'lrv-u-border-b-1', '' );
o_tease.c_span.c_span_text = 'Owen Gleiberman';
o_tease.c_span.c_span_classes = 'lrv-u-text-transform-uppercase u-font-family-accent lrv-u-font-size-24 u-letter-spacing-001 u-font-size-25@tablet u-border-b-1@tablet u-border-color-pale-sky-2 u-display-inline-flex lrv-u-margin-b-1@tablet';
o_tease.c_span.c_span_link_classes = o_tease.c_span.c_span_link_classes.replace( 'u-color-pale-sky-2', 'lrv-u-color-white' );
o_tease.c_span.c_span_link_classes = o_tease.c_span.c_span_link_classes.replace( 'lrv-u-padding-b-025', 'lrv-u-padding-b-050' );
o_tease.c_span.c_span_link_classes = o_tease.c_span.c_span_link_classes.replace( 'u-color-black:hover', 'u-color-brand-accent-20:hover' );
o_tease.c_span.c_span_link_classes += ' u-padding-b-00@tablet';
o_tease.c_lazy_image.c_lazy_image_classes += ' u-margin-lr-n075@mobile-max u-max-width-175@tablet u-max-width-225@desktop-xl';
o_tease.c_lazy_image.c_lazy_image_crop_class += ' a-crop-89x59@tablet';
o_tease.c_title.c_title_classes = 'lrv-u-font-family-primary u-font-size-22 lrv-u-font-weight-normal u-max-height-36em a-truncate-ellipsis u-line-height-110 u-font-size-21@tablet';
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-max-height-36em', 'u-max-height-36em@mobile-max' );
o_tease.c_title.c_title_link_classes = o_tease.c_title.c_title_link_classes.replace( 'lrv-u-color-black', 'lrv-u-color-white' );
o_tease.c_title.c_title_link_classes = o_tease.c_title.c_title_link_classes.replace( 'u-color-brand-secondary-50:hover', 'u-color-brand-accent-20:hover' );
o_tease.c_title.c_title_text = 'Are Martin Scorsese and Francis Ford Coppola Right About Marvel?';

module.exports = {
	...o_tease,
};
