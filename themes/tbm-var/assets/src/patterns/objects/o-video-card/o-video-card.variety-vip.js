const clonedeep = require( 'lodash.clonedeep' );

const o_video_card_prototype = require( './o-video-card.prototype.js' );
const o_video_card = clonedeep( o_video_card_prototype );

const {
	c_heading,
	c_play_icon,
	o_indicator,
} = o_video_card;

o_video_card.o_video_card_image_url = 'https://source.unsplash.com/random/831x468';
o_video_card.o_video_card_lazy_image_url = 'https://source.unsplash.com/random/831x468';
o_video_card.o_video_card_classes = 'lrv-a-glue-parent c-play-badge-parent';
o_video_card.o_video_card_crop_class = 'a-crop-166x89';
o_video_card.o_video_card_is_player = false;
o_video_card.o_video_card_meta_classes = 'lrv-u-background-color-black lrv-u-width-100p lrv-u-padding-lr-1 lrv-u-text-align-center@mobile-max u-padding-l-170@tablet u-padding-tb-150 u-position-relative';

c_heading.c_heading_text = 'Video title goes here';
c_heading.c_heading_link_classes = 'lrv-u-color-white';
c_heading.c_heading_classes = 'lrv-u-color-white lrv-u-font-weight-normal u-font-family-basic u-font-size-30 u-font-size-35@tablet u-margin-t-025@mobile-max u-line-height-120';
c_heading.c_heading_url = '#single-url';

c_play_icon.c_play_badge_classes = 'lrv-a-glue a-glue--a-50p@mobile-max u-transform-translate-a-n50p@mobile-max a-glue--b-n55@tablet a-glue--l-28@tablet u-width-70 u-height-70 u-width-115@tablet u-height-115@tablet is-to-be-hidden';

o_indicator.c_span.c_span_text = 'Entertainment & Tech Summit';
o_indicator.o_indicator_classes = '';
o_indicator.c_span.c_span_classes = '';
o_indicator.c_span.c_span_url = "#playlist_taxonomy_url";
o_indicator.c_span.c_span_link_classes = 'lrv-u-font-family-secondary u-font-size-13 u-font-size-18@tablet lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-letter-spacing-009@mobile-max u-letter-spacing-001 lrv-u-color-white:hover u-color-brand-vip-primary';

module.exports = {
	...o_video_card,
	c_span: null,
};
