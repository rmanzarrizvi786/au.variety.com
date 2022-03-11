const clonedeep = require( 'lodash.clonedeep' );

const o_video_card_prototype = require( './o-video-card.prototype.js' );
const o_video_card = clonedeep( o_video_card_prototype );

const {
	o_indicator,
	c_dek,
} = o_video_card;

o_video_card.o_video_card_classes += ' lrv-a-grid u-grid-gap-0 u-grid-gap-125@tablet a-cols3@tablet';
o_video_card.o_video_card_crop_class += ' a-span2@tablet';
o_video_card.o_video_card_permalink_classes += 'lrv-u-display-block u-width-640@tablet';
o_video_card.o_video_card_image_url = 'https://source.unsplash.com/random/640x360';
o_video_card.o_video_card_lazy_image_url = 'https://source.unsplash.com/random/640x360';
o_video_card.o_video_card_meta_classes = o_video_card.o_video_card_meta_classes.replace( 'lrv-u-padding-tb-050', 'u-padding-tb-050@mobile-max' );

o_indicator.o_indicator_classes += ' lrv-u-display-none';

c_dek.c_dek_classes = 'a-hidden@mobile-max lrv-u-color-white lrv-u-font-family-secondary lrv-u-font-size-16 u-margin-t-050@tablet u-margin-b-050@tablet';
c_dek.c_dek_text = 'Joaquin Phoenix has three Academy Award nominations, four Golden Globe nominations (and one win) and a lead role in another film that’s already …';

module.exports = {
	...o_video_card,
};
