const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.voices.primary' );
const o_tease = clonedeep( o_tease_prototype );

o_tease.o_tease_classes += ' lrv-u-padding-lr-1@tablet';
o_tease.c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/350x460';
o_tease.c_lazy_image.c_lazy_image_crop_class = 'a-crop-35x46 a-crop-89x59@tablet';
o_tease.c_lazy_image.c_lazy_image_classes = 'a-hidden@mobile-max u-border-color-brand-secondary-50';
o_tease.c_span = null;
o_tease.c_title.c_title_classes = 'lrv-u-font-family-secondary lrv-u-font-weight-bold u-font-size-15 lrv-u-margin-t-050 u-font-size-16@tablet u-margin-t-075@tablet u-line-height-120';

module.exports = {
	...o_tease,
};
