const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( '../../objects/o-tease/o-tease.square' );
const o_tease = clonedeep( o_tease_prototype );

o_tease.o_tease_classes += ' u-padding-t-125@tablet';
o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'u-padding-b-150@tablet', 'u-padding-b-125@tablet' );
o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'u-padding-b-175@desktop-xl', '' );
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-width-50@tablet', 'u-width-105@tablet' );
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-margin-r-125@tablet', 'lrv-u-margin-r-1@tablet' );
o_tease.c_lazy_image.c_lazy_image_crop_class = 'a-crop-3x2 a-crop-1x1@tablet';
o_tease.c_title.c_title_link_classes += ' u-color-white@tablet u-color-brand-accent-20:hover@tablet';

module.exports = {
	...o_tease,
};
