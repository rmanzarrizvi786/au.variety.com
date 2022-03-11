const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.variety-vip.js' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const o_slide_prototype = require( '../../objects/o-slide/o-slide.prototype.js' );
const o_slide = clonedeep( o_slide_prototype );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.prototype.js' );
const o_more_link = clonedeep( o_more_link_prototype );

o_more_from_heading.c_v_icon = null;
o_more_from_heading.c_heading.c_heading_text = 'Explore All Events';
o_more_from_heading.o_more_from_heading_classes = 'lrv-u-flex u-justify-content-center@mobile-max lrv-u-align-items-center lrv-u-padding-tb-050';
o_more_from_heading.c_heading.c_heading_classes = o_more_from_heading.c_heading.c_heading_classes.replace( 'u-letter-spacing-021', 'u-letter-spacing-040@mobile-max' );
o_more_from_heading.c_heading.c_heading_classes += ' u-font-family-secondary@tablet';

o_more_link.o_more_link_classes = 'lrv-u-text-align-center u-margin-t-150';
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'a-icon-long-right-arrow', 'a-icon-plus-background' );
o_more_link.c_link.c_link_text = 'Load more events';

o_slide.o_slide_classes = o_slide.o_slide_classes.replace( 'a-scale-110@tablet:hover', '' );
o_slide.o_slide_meta_classes = 'lrv-u-display-none';
o_slide.c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/315x215';
o_slide.c_lazy_image.c_lazy_image_classes = o_slide.c_lazy_image.c_lazy_image_classes.replace( 'u-margin-b-075', '' );
o_slide.c_lazy_image.c_lazy_image_crop_class = 'a-crop-317x215 a-crop-277x214@tablet';
o_slide.o_slide_classes = o_slide.o_slide_classes.replace( 'u-width-300@tablet', 'lrv-u-width-100p' );
o_slide.o_slide_classes = o_slide.o_slide_classes.replace( 'u-width-215', 'lrv-u-width-100p' );
o_slide.o_indicator = null;
o_slide.c_title.c_title_text = 'Entertainment & Tech Summit';
o_slide.c_title.c_title_classes = 'lrv-u-font-size-16 lrv-u-font-family-secondary lrv-u-text-transform-uppercase u-letter-spacing-001 u-font-size-19@tablet';
o_slide.c_title.c_title_link_classes = 'lrv-u-color-brand-primary lrv-u-display-block u-color-black:hover lrv-u-padding-tb-1';

const o_more_link_desktop = clonedeep( o_more_link );

o_more_link.o_more_link_classes += ' a-hidden@tablet';

o_more_link_desktop.o_more_link_classes = 'a-hidden@mobile-max';

const explore_all_events_items = [
	o_slide,
	o_slide,
	o_slide,
	o_slide,
];

const explore_all_events_hidden_items = clonedeep( explore_all_events_items );

module.exports = {
	explore_all_events_carousel_classes: 'u-background-image-slash u-border-t-6 u-border-b-6 u-border-color-brand-secondary-50 lrv-u-padding-b-1 u-padding-t-1@tablet u-padding-b-250@tablet',
	explore_all_events_inner_classes: 'lrv-a-wrapper lrv-a-grid lrv-a-cols4@tablet u-padding-lr-150 u-grid-gap-175',
	explore_all_events_item_classes: 'lrv-u-margin-lr-050 lrv-u-padding-tb-1@tablet u-margin-r-250@tablet',
	explore_all_events_inner_hidden_classes: 'lrv-a-wrapper lrv-a-grid lrv-a-cols4@tablet u-padding-lr-150 u-grid-gap-175 u-margin-t-175',
	explore_all_events_item_hidden_classes: 'lrv-u-margin-lr-050 lrv-u-padding-tb-1@tablet u-margin-r-250@tablet',
	o_more_from_heading,
	explore_all_events_items,
	o_more_link,
	o_more_link_desktop,
	explore_all_events_hidden_items,
};
