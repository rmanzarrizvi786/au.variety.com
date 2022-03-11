const clonedeep = require( 'lodash.clonedeep' );

const o_video_card_prototype = require( './o-video-card.small.js' );
const o_video_card = clonedeep( o_video_card_prototype );

const {
	c_heading
} = o_video_card;

o_video_card.o_video_card_classes = o_video_card.o_video_card_classes.replace( 'u-width-125', '' );
o_video_card.o_video_card_classes = o_video_card.o_video_card_classes.replace( 'lrv-u-flex@tablet', 'lrv-u-flex' );
o_video_card.o_video_card_classes = o_video_card.o_video_card_classes.replace( 'u-padding-t-125@tablet', 'u-padding-t-075@tablet' );
o_video_card.o_video_card_classes += ' u-margin-lr-075@mobile-max u-padding-t-075 u-border-t-1@mobile-max u-border-color-pale-sky-2';
o_video_card.o_video_card_meta_classes = o_video_card.o_video_card_meta_classes.replace( 'u-margin-t-050@mobile-max', '' );
o_video_card.o_video_card_meta_classes = o_video_card.o_video_card_meta_classes.replace( 'u-margin-l-050@tablet', 'u-margin-l-1@tablet' );
o_video_card.o_video_card_meta_classes = o_video_card.o_video_card_meta_classes.replace( 'u-justify-content-space-between@tablet', '' );
o_video_card.o_video_card_meta_classes += ' u-order-n1@mobile-max';
o_video_card.o_video_card_crop_class += ' u-width-160 u-width-240@desktop-xl';
o_video_card.o_video_card_image_url = 'https://source.unsplash.com/random/240x135';
o_video_card.o_video_card_lazy_image_url = 'https://source.unsplash.com/random/240x135';

c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'u-font-size-12@tablet', '' );
c_heading.c_heading_text = 'Oscars Best Actor 2020: Who Will Win?';

module.exports = {
	...o_video_card,
};
