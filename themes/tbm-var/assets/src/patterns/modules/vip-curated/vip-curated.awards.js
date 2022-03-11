const clonedeep = require( 'lodash.clonedeep' );

const vip_curated_prototype = require( './vip-curated.prototype' );
const vip_curated = clonedeep( vip_curated_prototype );

const {
	o_more_from_heading,
	o_tease_primary,
	o_tease_list,
	o_more_link,
} = vip_curated;

vip_curated.vip_curated_classes = vip_curated.vip_curated_classes.replace( 'u-border-color-vip-brand-primary', 'u-border-color-picked-bluewood' );
vip_curated.vip_curated_classes = vip_curated.vip_curated_classes.replace( 'u-padding-b-150', '' );

vip_curated.vip_curated_stories_classes = vip_curated.vip_curated_stories_classes.replace( 'u-margin-t-1@tablet', '' );

o_more_from_heading.c_heading.c_heading_classes = o_more_from_heading.c_heading.c_heading_classes.replace( ' lrv-a-screen-reader-only', '' );
o_more_from_heading.c_heading.c_heading_text = 'Awards';
o_more_from_heading.c_v_icon = null;

o_tease_primary.o_tease_classes += ' ';

o_tease_primary.o_tease_classes += ' u-padding-b-100@desktop-xl';
o_tease_primary.c_title.c_title_classes = o_tease_primary.c_title.c_title_classes.replace( 'u-font-size-24@tablet', 'u-font-size-24@desktop-xl' );
o_tease_primary.c_title.c_title_classes += ' u-font-size-16@tablet u-font-size-24@desktop-xl u-font-family-secondary@tablet u-font-family-primary@desktop-xl u-font-weight-bold@tablet u-font-weight-normal@desktop-xl';
o_tease_primary.c_title.c_title_link_classes += ' u-color-brand-accent-80:hover';
o_tease_primary.c_lazy_image.c_lazy_image_crop_class = o_tease_primary.c_lazy_image.c_lazy_image_crop_class.replace( 'a-crop-605x413@tablet', 'a-crop-605x413@desktop-xl' );

o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'lrv-u-flex@tablet', 'u-flex@desktop-xl' );
o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'lrv-a-space-children-horizontal@tablet', '' );
o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'a-separator-r-1@tablet', 'a-separator-r-1@desktop-xl' );
o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'a-separator-spacing--r-1@tablet', 'a-separator-spacing--r-1@desktop-xl' );
o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'u-padding-b-150@tablet', 'u-padding-b-150@desktop-xl' );
o_tease_list.o_tease_list_classes += ' a-space-children-vertical@desktop-xl-max a-separator-b-1@desktop-xl-max u-padding-b-00@tablet';

o_tease_list.o_tease_list_items.forEach( item => {
	item.o_tease_classes = item.o_tease_classes.replace( 'lrv-u-padding-r-1@tablet', 'u-padding-r-1@desktop-xl' );
	item.o_tease_classes = item.o_tease_classes.replace( 'u-padding-b-125@tablet', 'u-padding-b-150@tablet' );

	item.c_title.c_title_link_classes = item.c_title.c_title_link_classes.replace( 'u-color-brand-secondary-50:hover', 'u-color-brand-accent-80:hover' );

	item.c_lazy_image.c_lazy_image_crop_class = item.c_lazy_image.c_lazy_image_crop_class.replace( 'lrv-a-crop-16x9', 'a-crop-3x2' );
	item.c_lazy_image.c_lazy_image_crop_class = item.c_lazy_image.c_lazy_image_crop_class.replace( 'a-crop-258x125@tablet', 'a-crop-16x9@tablet' );
} );

o_more_link.o_more_link_classes = o_more_link.o_more_link_classes.replace( 'u-border-b-1@mobile-max', '' );
o_more_link.c_link.c_link_text = 'More Awards';
o_more_link.c_link.c_link_classes += ' u-color-brand-accent-80:hover';

module.exports = {
	...vip_curated
};
