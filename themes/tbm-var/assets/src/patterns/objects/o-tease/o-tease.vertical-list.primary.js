const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.latest-from.vip' );
const o_tease = clonedeep( o_tease_prototype );

o_tease.o_tease_classes += ' lrv-u-margin-b-1@tablet u-padding-b-125@tablet';
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-size-22', 'u-font-size-15' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-size-24@tablet', 'u-font-size-16@tablet' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'lrv-u-font-family-primary', 'lrv-u-font-family-secondary' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'lrv-u-font-weight-normal', 'lrv-u-font-weight-bold' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-margin-t-075', 'u-margin-t-050' );
o_tease.c_lazy_image.c_lazy_image_classes = o_tease.c_lazy_image.c_lazy_image_classes.replace( 'u-margin-lr-n050@mobile-max', '' );
o_tease.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-16x9';

module.exports = {
	...o_tease,
};
