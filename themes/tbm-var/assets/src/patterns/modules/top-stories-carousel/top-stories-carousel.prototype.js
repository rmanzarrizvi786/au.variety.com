const clonedeep = require( 'lodash.clonedeep' );

const o_top_story_prototype = require( '../../objects/o-top-story/o-top-story.prototype.js' );
const o_top_story_primary = clonedeep( o_top_story_prototype );

const cxense_widget_prototype = require( '../cxense-widget/cxense-widget.prototype' );
const cxense_carousel_widget = clonedeep( cxense_widget_prototype );

o_top_story_primary.is_primary = true;
o_top_story_primary.c_lazy_image.c_lazy_image_classes = 'a-overlay--b-t35p@tablet u-padding-b-1@tablet u-padding-a-1@mobile-max lrv-u-margin-lr-1 lrv-u-margin-t-050 u-margin-a-00@tablet';
o_top_story_primary.o_top_story_classes = o_top_story_primary.o_top_story_classes.replace( 'a-crop-375x575@mobile-max', 'a-crop-4x3@mobile-max' );
o_top_story_primary.o_top_story_classes += ' lrv-u-display-block lrv-u-width-100p ';
o_top_story_primary.c_title.c_title_classes = 'lrv-u-font-family-secondary lrv-u-font-family-primary@tablet lrv-u-text-align-center u-text-transform-uppercase@tablet u-font-weight-bold@mobile-max u-font-size-70@tablet u-font-weight-medium u-letter-spacing-2 u-line-height-1 u-font-size-28@mobile-max u-margin-b-1@mobile-max';

o_top_story_primary.c_dek.c_dek_classes = o_top_story_primary.c_dek.c_dek_classes.replace( 'a-hidden@mobile-max', '' );
o_top_story_primary.c_dek.c_dek_classes += ' lrv-u-padding-b-2';

const top_stories_carousel = [
	o_top_story_primary
];

cxense_carousel_widget.cxense_id_attr = 'cx-module-top-stories-carousel';

module.exports = {
	top_stories_carousel_classes: 'lrv-a-grid lrv-a-cols3@tablet u-grid-gap-0 u-align-items-stretch lrv-u-overflow-hidden u-margin-lr-n050',
	top_stories_carousel_flickity_outer_classes: 'lrv-a-span2@tablet js-Flickity--nav-top-right js-Flickity--hide-nav@mobile-max',
	top_stories_carousel,
	cxense_carousel_widget
};
