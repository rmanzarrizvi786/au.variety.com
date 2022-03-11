const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.prototype' );
const o_tease = clonedeep( o_tease_prototype );

o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'lrv-u-padding-b-1', 'u-padding-b-125' );
o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'lrv-u-align-items-center', '' );
o_tease.o_tease_classes += ' u-padding-b-150@tablet u-padding-t-1@desktop-xl u-padding-b-175@desktop-xl';
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-width-177@tablet', 'u-width-50@tablet' );
o_tease.c_lazy_image.c_lazy_image_crop_class += ' a-crop-1x1@tablet';
o_tease.c_link = null;
o_tease.c_timestamp = null;

module.exports = {
	...o_tease,
};
