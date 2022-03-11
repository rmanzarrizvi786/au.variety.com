const clonedeep = require( 'lodash.clonedeep' );

const o_video_card_prototype = require( './o-video-card.prototype.js' );
const o_video_card = clonedeep( o_video_card_prototype );

const {
	c_heading,
	c_play_icon,
	c_span,
	o_indicator,
} = o_video_card;

o_video_card.o_video_card_classes = o_video_card.o_video_card_classes.replace( 'u-max-width-595', 'u-width-125' );
o_video_card.o_video_card_classes += ' lrv-u-flex@tablet u-width-100p@tablet u-padding-t-125@tablet u-padding-b-1@tablet';
o_video_card.o_video_card_meta_classes = o_video_card.o_video_card_meta_classes.replace( 'lrv-u-padding-tb-050', '' );
o_video_card.o_video_card_meta_classes = o_video_card.o_video_card_meta_classes.replace( 'u-padding-lr-075@mobile-max', '' );
o_video_card.o_video_card_meta_classes = o_video_card.o_video_card_meta_classes.replace( 'lrv-u-width-100p', '' );
o_video_card.o_video_card_meta_classes += ' u-margin-t-050@mobile-max u-margin-l-050@tablet u-justify-content-space-between@tablet';
o_video_card.o_video_card_crop_data_attr = 'Now Playing';
o_video_card.o_video_card_crop_class = o_video_card.o_video_card_crop_class.replace( 'lrv-a-crop-16x9', '' );
o_video_card.o_video_card_crop_class += ' u-width-175@tablet lrv-u-flex-shrink-0';
o_video_card.o_video_card_image_classes = o_video_card.o_video_card_image_classes.replace( 'is-to-be-hidden', '' );
o_video_card.o_video_card_is_player = false;
o_video_card.o_video_card_image_url = 'https://source.unsplash.com/random/175x98';
o_video_card.o_video_card_lazy_image_url = 'https://source.unsplash.com/random/175x98';

c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'lrv-u-font-family-primary', 'lrv-u-font-family-secondary' );
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'lrv-u-font-size-24', '' );
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'u-font-size-32@tablet', 'u-font-size-12@tablet' );
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'u-font-size-36@desktop-xl', 'u-font-size-14@desktop-xl' );
c_heading.c_heading_classes += ' u-font-size-13';
c_heading.c_heading_link_classes = 'lrv-u-color-white lrv-u-font-weight-bold lrv-u-display-block';

c_play_icon.c_play_badge_classes = 'lrv-u-display-none';

c_span.c_span_classes = c_span.c_span_classes.replace( 'lrv-u-font-size-14', 'lrv-u-font-size-14@tablet' );
c_span.c_span_classes += ' lrv-u-font-size-10';

o_indicator.o_indicator_classes = 'lrv-u-display-none';

module.exports = {
	...o_video_card
};
