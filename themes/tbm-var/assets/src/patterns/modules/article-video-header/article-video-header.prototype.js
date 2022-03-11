const clonedeep = require( 'lodash.clonedeep' );

const o_video_card_prototype = require( '../../objects/o-video-card/o-video-card.prototype.js' );
const o_video_card = clonedeep( o_video_card_prototype );

const social_share_prototype = require( '../social-share/social-share.variety-vip.js' );
const social_share = clonedeep( social_share_prototype );

const c_timestamp_prototype = require( '@penskemediacorp/larva-patterns/components/c-timestamp/c-timestamp.prototype.js' );
const c_timestamp = clonedeep( c_timestamp_prototype );

const o_indicator_prototype = require( '../../objects/o-indicator/o-indicator.prototype.js' );
const o_indicator = clonedeep( o_indicator_prototype );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

o_video_card.c_play_icon.c_play_badge_classes = 'lrv-a-glue a-glue--a-50p@mobile-max u-transform-translate-a-n50p@mobile-max a-glue--b-n55@tablet a-glue--l-28@tablet u-width-70 u-height-70 u-width-115@tablet u-height-115@tablet is-to-be-hidden';
o_video_card.o_video_card_meta_classes = 'lrv-u-background-color-black lrv-u-width-100p a-hidden@mobile-max u-padding-l-170 u-padding-tb-150 u-position-relative';
o_video_card.o_video_card_is_player = true;
o_video_card.o_video_card_permalink_url = '';
o_video_card.c_heading.c_heading_classes = 'lrv-u-color-white lrv-u-font-weight-normal u-font-family-basic u-font-size-35';
o_video_card.c_heading.c_heading_is_primary_heading = true;
o_video_card.o_video_card_crop_class = 'a-crop-166x89';
o_video_card.o_indicator.c_span.c_span_text = 'Entertainment & Tech Summit';
o_video_card.o_indicator.o_indicator_classes = '';
o_video_card.o_indicator.c_span.c_span_classes = 'lrv-u-font-family-secondary lrv-u-font-size-18 lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-letter-spacing-001';
o_video_card.o_indicator.c_span.c_span_url = "#playlist-taxonomy-url";
o_video_card.o_indicator.c_span.c_span_link_classes = 'lrv-u-color-white:hover';

social_share.social_share_classes = 'lrv-u-justify-content-center lrv-u-padding-lr-2@mobile-max';
social_share.social_share_item_classes = 'lrv-u-flex lrv-u-align-items-center lrv-u-margin-b-050@mobile-max';
social_share.plus_icon.c_icon_link_classes = 'lrv-u-border-color-grey-light lrv-u-display-block u-display-inline-flex u-color-brand-secondary-50 lrv-u-padding-l-050';

c_timestamp.c_timestamp_classes += ' lrv-u-margin-t-1 u-margin-b-125 u-padding-lr-2@mobile-max u-font-size-13 u-font-family-accent u-color-iron-grey lrv-u-text-transform-uppercase u-letter-spacing-003';
c_timestamp.c_timestamp_text = 'AUGUST 29, 2016 3:10PM';

o_indicator.o_indicator_classes = 'lrv-u-padding-lr-2 lrv-u-margin-t-2 lrv-u-margin-b-1 a-hidden@tablet';
o_indicator.c_span.c_span_classes = '';
o_indicator.c_span.c_span_link_classes = 'lrv-u-font-family-secondary u-font-size-15 lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-letter-spacing-001';
o_indicator.c_span.c_span_url = '#taxonomy-url';
o_indicator.c_span.c_span_text = 'Variety Entertainment & Technology Summit';

c_heading.c_heading_text = 'John Landgraf Breaks Down FX’s Streaming Strategy';
c_heading.c_heading_is_primary_heading = true;
c_heading.c_heading_classes = 'lrv-u-text-transform-uppercase a-hidden@tablet lrv-u-font-family-primary lrv-u-padding-lr-2 u-font-size-50 u-line-height-1';

module.exports = {
	big_video_classes: 'lrv-a-glue-parent',
	big_video_video_classes: 'lrv-u-margin-lr-auto u-margin-t-312@tablet u-max-width-830',
	big_video_background_classes: 'lrv-a-glue lrv-a-glue--t-0 lrv-u-width-100p lrv-u-background-color-white lrv-u-border-color-black a-hidden@mobile-max u-z-index-base u-background-image-slash',
	big_video_meta_classes: 'lrv-u-flex lrv-u-flex-direction-column@mobile-max lrv-align-items-center lrv-u-text-align-center lrv-u-margin-lr-auto lrv-u-margin-b-1@mobile-max lrv-u-margin-b-2 lrv-u-padding-lr-1@tablet u-padding-lr-00@desktop u-margin-t-050@tablet u-max-width-830 u-justify-content-space-between@tablet',
	is_article: true,
	o_video_card,
	social_share,
	c_timestamp,
	o_indicator,
	c_heading,
};
