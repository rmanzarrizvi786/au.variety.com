const clonedeep = require( 'lodash.clonedeep' );

const o_video_card_prototype = require( './o-video-card.small' );
const o_video_card = clonedeep( o_video_card_prototype );

o_video_card.o_video_card_classes += ' u-width-175@mobile-max';

o_video_card.c_heading.c_heading_classes = o_video_card.c_heading.c_heading_classes.replace( 'u-font-size-12@tablet', 'u-font-size-14@desktop-xl' );
o_video_card.c_heading.c_heading_classes = o_video_card.c_heading.c_heading_classes.replace( 'u-line-height-small@tablet', '' );
o_video_card.c_heading.c_heading_link_classes += ' a-truncate-ellipsis';

module.exports = {
	...o_video_card,
};
