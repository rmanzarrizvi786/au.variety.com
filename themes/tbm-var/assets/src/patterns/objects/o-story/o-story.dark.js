const clonedeep = require( 'lodash.clonedeep' );

const o_story = clonedeep( require( './o-story.prototype' ) );

o_story.o_story_classes = 'u-padding-a-075@mobile-max u-background-color-picked-bluewood u-border-t-6@mobile-max u-border-color-pale-sky-2 lrv-u-flex-direction-column@tablet';
o_story.o_story_primary_classes = 'lrv-u-padding-tb-075 lrv-u-padding-lr-1';
o_story.o_story_secondary_classes = 'u-max-width-125@mobile-max u-order-n1 lrv-u-width-100p';
o_story.c_span = false;
o_story.c_title.c_title_classes = 'a-font-primary-regular-m lrv-u-color-white';
o_story.c_title.c_title_link_classes = 'u-color-brand-secondary-40:hover';
o_story.c_link.c_link_classes = 'a-font-secondary-bold-xs u-color-brand-secondary-40 u-color-brand-accent-80:hover';
o_story.o_story_meta_classes = '';
o_story.c_lazy_image.c_lazy_image_classes += ' u-border-b-6@tablet u-border-color-pale-sky-2';
o_story.c_lazy_image.c_lazy_image_crop_class = 'lrv-u-height-100p lrv-a-crop-1x1 a-crop-3x2@desktop';
o_story.c_dek = false;
o_story.c_timestamp = false;

module.exports = o_story;
