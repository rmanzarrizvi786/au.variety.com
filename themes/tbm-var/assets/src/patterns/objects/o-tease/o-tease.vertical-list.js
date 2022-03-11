const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.latest-from' );
const o_tease = clonedeep( o_tease_prototype );

o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'lrv-u-padding-b-1', 'u-padding-b-125' );
o_tease.o_tease_classes += ' lrv-u-border-b-1 u-border-color-brand-secondary-40 u-padding-b-250@tablet';
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-size-18@tablet', 'u-font-size-16@tablet' );
o_tease.c_title.c_title_classes += ' u-margin-t-050@tablet';
o_tease.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-16x9';
o_tease.c_span = null;
o_tease.c_link = null;
o_tease.c_timestamp = null;

module.exports = {
	...o_tease
};
