const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.latest-from.vip' );
const o_tease = clonedeep( o_tease_prototype );

o_tease.o_tease_classes += ' u-padding-b-2@tablet u-padding-b-150@desktop-xl';
o_tease.c_title.c_title_text = 'Amazon on top: ‘Fleabag’ Nearly Sweeps 2019 Comedy Emmys';
o_tease.c_title.c_title_link_classes = 'lrv-u-color-white lrv-u-display-block u-color-brand-secondary-50:hover u-color-brand-accent-20:hover@tablet';

o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-size-24@tablet', 'u-font-size-21@tablet' );
o_tease.c_title.c_title_classes += ' u-font-size-24@desktop-xl';
o_tease.c_lazy_image.c_lazy_image_classes = o_tease.c_lazy_image.c_lazy_image_classes.replace( 'u-margin-lr-n050@mobile-max', 'u-margin-lr-n075' );
o_tease.c_lazy_image.c_lazy_image_classes += ' u-margin-lr-n1@desktop-xl';
o_tease.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-1x1';

module.exports = {
	...o_tease
};
