const clonedeep = require( 'lodash.clonedeep' );

const o_video_card_prototype = require( './o-video-card.variety-vip.js' );
const o_video_card = clonedeep( o_video_card_prototype );

const { o_indicator, c_heading, c_play_icon } = o_video_card;

o_video_card.o_video_card_meta_classes = 'lrv-u-background-color-black lrv-u-width-100p lrv-u-text-align-center@mobile-max u-padding-l-170@tablet lrv-u-padding-t-050 u-padding-tb-150@tablet u-position-relative u-background-transparent@mobile-max';
o_video_card.o_video_card_crop_class += ' a-crop-4x3@mobile-max';

o_indicator.o_indicator_classes += ' a-hidden@mobile-max';

c_heading.c_heading_classes = 'lrv-u-color-white lrv-u-font-weight-normal lrv-u-font-family-secondary u-font-family-primary@tablet u-font-size-15 u-font-size-35@tablet u-font-weight-bold@mobile-max u-margin-t-025@mobile-max u-line-height-120 u-min-height-24em u-max-height-24em a-truncate-ellipsis';
c_heading.c_heading_link_classes = c_heading.c_heading_link_classes.replace( 'lrv-u-color-white', 'lrv-u-color-black u-color-white@tablet' );
c_heading.c_heading_text = 'Landgraf Breaks Down FX Streaming';

c_play_icon.c_play_badge_classes = c_play_icon.c_play_badge_classes.replace( 'u-width-70', 'u-width-54' );
c_play_icon.c_play_badge_classes = c_play_icon.c_play_badge_classes.replace( 'u-height-70', 'u-height-54' );

module.exports = {
	...o_video_card,
};
