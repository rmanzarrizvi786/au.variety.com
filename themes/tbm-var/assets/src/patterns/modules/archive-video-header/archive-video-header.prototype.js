const clonedeep = require( 'lodash.clonedeep' );

const o_video_card_prototype = require( '../../objects/o-video-card/o-video-card.prototype.js' );
const o_video_card = clonedeep( o_video_card_prototype );

o_video_card.c_play_icon.c_play_badge_classes = 'lrv-a-glue a-glue--a-50p@mobile-max u-transform-translate-a-n50p@mobile-max a-glue--b-n55@tablet a-glue--l-28@tablet u-width-70 u-height-70 u-width-115@tablet u-height-115@tablet is-to-be-hidden';
o_video_card.o_video_card_meta_classes = 'lrv-u-background-color-black lrv-u-width-100p lrv-u-padding-lr-1 lrv-u-text-align-center@mobile-max u-padding-l-170@tablet u-padding-tb-150 u-padding-b-175@tablet u-position-relative';
o_video_card.o_video_card_is_player = true;
o_video_card.o_video_card_permalink_url = '#single-url';
o_video_card.c_heading.c_heading_text = 'Video title goes here';
o_video_card.c_heading.c_heading_classes = 'lrv-u-color-white lrv-u-font-weight-normal u-font-family-basic u-font-size-30 u-font-size-35@tablet u-margin-t-025@mobile-max u-line-height-120';
o_video_card.c_heading.c_heading_link_classes = 'lrv-u-color-white';
o_video_card.c_heading.c_heading_url = '#single-url';
o_video_card.c_heading.c_heading_is_primary_heading = true;
o_video_card.o_video_card_crop_class = 'a-crop-166x89';
o_video_card.o_indicator.c_span.c_span_text = 'Entertainment & Tech Summit';
o_video_card.o_indicator.o_indicator_classes = '';
o_video_card.o_indicator.c_span.c_span_classes = '';
o_video_card.o_indicator.c_span.c_span_url = "#playlist_taxonomy_url";
o_video_card.o_indicator.c_span.c_span_link_classes = 'lrv-u-font-family-secondary u-font-size-13 u-font-size-18@tablet lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-letter-spacing-009@mobile-max u-letter-spacing-001 lrv-u-color-white:hover';
o_video_card.c_span = null;

module.exports = {
	big_video_video_classes: 'lrv-u-margin-lr-auto u-margin-t-312@tablet u-margin-b-437@tablet u-max-width-830 u-border-b-6@mobile-max u-border-color-dusty-grey',
	big_video_background_classes: 'lrv-a-glue lrv-a-glue--t-0 lrv-u-border-color-black a-hidden@mobile-max u-z-index-base lrv-u-background-color-black',
	big_video_meta_classes: 'lrv-u-flex lrv-u-flex-direction-column@mobile-max lrv-align-items-center lrv-u-text-align-center lrv-u-margin-lr-auto lrv-u-margin-b-1@mobile-max lrv-u-margin-b-2 lrv-u-padding-lr-1@tablet u-padding-lr-00@desktop u-margin-t-050@tablet u-max-width-830 u-justify-content-space-between@tablet',
	o_video_card,
};
