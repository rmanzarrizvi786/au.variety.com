const clonedeep = require( 'lodash.clonedeep' );

const o_video_card_prototype = require( './o-video-card.prototype.js' );
const o_video_card = clonedeep( o_video_card_prototype );

const {
	c_play_icon,
	c_heading,
	c_span,
	o_indicator,
} = o_video_card;

o_video_card.o_video_card_classes = o_video_card.o_video_card_classes.replace( 'u-max-width-595', '' );
o_video_card.o_video_card_classes += ' u-width-250@tablet u-width-390@desktop-xl lrv-u-height-100p';
o_video_card.o_video_card_crop_class += ' lrv-u-margin-b-025';
o_video_card.o_video_card_meta_classes += ' u-padding-b-00@tablet';

c_play_icon.c_play_badge_classes = c_play_icon.c_play_badge_classes.replace( 'u-width-40', 'u-width-30' );
c_play_icon.c_play_badge_classes = c_play_icon.c_play_badge_classes.replace( 'u-height-40', 'u-height-30' );
c_play_icon.c_play_badge_classes = c_play_icon.c_play_badge_classes.replace( 'a-glue--b-25', 'a-glue--b-15' );
c_play_icon.c_play_badge_classes += ' a-hidden@tablet';

c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'lrv-u-font-size-24', 'u-font-size-22' );
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'u-font-size-32@tablet', 'u-font-size-18@tablet' );
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'u-font-size-36@desktop-xl', 'u-font-size-24@tablet' );
c_heading.c_heading_classes += ' u-margin-b-050@tablet';
c_heading.c_heading_text = 'Robert Pattinson on \'The Lighthouse\' | Between the Lines';

c_span.c_span_classes = c_span.c_span_classes.replace( 'lrv-u-font-size-14', 'lrv-u-font-size-10' );
c_span.c_span_classes += ' lrv-u-font-size-14@tablet';

o_indicator.o_indicator_classes += ' lrv-u-display-none';

module.exports = {
	...o_video_card,
};
