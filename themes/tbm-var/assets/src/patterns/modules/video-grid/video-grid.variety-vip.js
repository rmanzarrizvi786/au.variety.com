const clonedeep = require( 'lodash.clonedeep' );

const o_video_card_prototype = require( '../../objects/o-video-card/o-video-card.variety-vip.js' );
const o_video_card = clonedeep( o_video_card_prototype );

o_video_card.o_video_card_classes = 'u-border-b-1@mobile-max u-border-color-loblolly-grey';
o_video_card.c_play_icon.c_play_badge_classes = 'lrv-a-glue a-glue--a-50p u-transform-translate-a-n50p u-width-70 u-height-70 is-to-be-hidden';
o_video_card.o_video_card_meta_classes = 'lrv-u-width-100p u-padding-t-150 u-padding-b-125 u-padding-tb-150@tablet u-padding-lr-075@tablet';
o_video_card.o_video_card_permalink_url = '#single_url'
o_video_card.c_heading.c_heading_url = '#single_url';
o_video_card.c_heading.c_heading_classes = 'test lrv-u-font-weight-normal lrv-u-text-align-center u-font-family-basic u-font-size-30 u-line-height-120 u-margin-t-075';
o_video_card.c_heading.c_heading_link_classes = 'lrv-u-color-black lrv-u-display-block';
o_video_card.o_video_card_crop_class = 'a-crop-277x215 lrv-a-glue-parent a-scale-110:hover u-box-shadow-video:hover';
o_video_card.o_indicator.c_span.c_span_text = 'Entertainment & Tech Summit';
o_video_card.o_indicator.o_indicator_classes = '';
o_video_card.o_indicator.c_span.c_span_classes = 'lrv-u-display-block lrv-u-font-family-secondary lrv-u-text-align-center u-font-size-13 lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-letter-spacing-001';
o_video_card.o_indicator.c_span.c_span_link_classes = 'lrv-u-color-grey-dark:hover';
o_video_card.o_indicator.c_span.c_span_url = '#playlist_taxonomy_url';

const video_items = [
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
];

module.exports = {
	video_grid_classes: 'lrv-a-wrapper lrv-a-cols4@tablet u-grid-gap-150 u-grid-gap-262@tablet',
	play_in_place: false,
	video_items,
};
