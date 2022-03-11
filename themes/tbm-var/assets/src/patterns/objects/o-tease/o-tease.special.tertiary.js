const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.voices.secondary' );
const o_tease = clonedeep( o_tease_prototype );

o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'lrv-u-padding-b-050', 'lrv-u-padding-b-1' );
o_tease.o_tease_classes += ' lrv-u-padding-lr-1@tablet';
o_tease.o_tease_secondary_classes += ' a-hidden@mobile-max';
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'lrv-u-font-weight-normal', '' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-weight-normal@tablet', '' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-family-primary@tablet', '' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-size-21@tablet', '' );
o_tease.c_title.c_title_classes += ' lrv-u-margin-t-050 u-font-size-16@tablet u-margin-t-075@tablet';
o_tease.c_span = null;
o_tease.c_lazy_image.c_lazy_image_classes = 'u-border-color-brand-secondary-50';

module.exports = {
	...o_tease,
};
