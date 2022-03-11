const clonedeep = require( 'lodash.clonedeep' );

const o_story = clonedeep( require( './o-story.prototype' ) );

o_story.c_span.c_span_url = false;

o_story.c_title.c_title_classes = 'a-font-secondary-bold-3xl';

o_story.c_dek.c_dek_classes = o_story.c_dek.c_dek_classes.replace( 'a-hidden@mobile-max ', '' );

o_story.c_dek.c_dek_classes = 'a-font-primary-regular-s u-margin-tb-025 lrv-u-font-size-20@desktop';

o_story.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-16x9 a-crop-4x3@desktop lrv-u-height-100p';

o_story.video_permalink_url = '#';

o_story.o_story_classes = 'o-story--primary lrv-u-flex-direction-column@mobile-max u-padding-lr-075 u-padding-tb-075@tablet lrv-u-background-color-white';
o_story.o_story_secondary_classes = 'u-width-100p@tablet u-width-500@desktop-xl u-flex-shrink-0@desktop-xl u-order-n1 u-margin-lr-n075@mobile-max';
o_story.o_story_primary_classes += ' u-margin-t-075@mobile-max u-margin-b-075@mobile-max';

o_story.c_play_badge.c_play_badge_classes = o_story.c_play_badge.c_play_badge_classes.replace( ' lrv-a-glue@tablet', ' lrv-a-glue' );

o_story.c_lazy_image_play_badge_classes = '';

module.exports = o_story;
