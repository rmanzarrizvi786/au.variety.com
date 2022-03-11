const clonedeep = require( 'lodash.clonedeep' );

const video_showcase_prototype = require( './video-showcase.prototype' );
const video_showcase = clonedeep( video_showcase_prototype );

const {
	o_video_card,
	related_videos,
} = video_showcase;

video_showcase.video_header_videos_classes = video_showcase.video_header_videos_classes.replace( 'u-padding-b-125@tablet', 'u-padding-b-2@tablet' );
video_showcase.video_header_data_attrs = 'data-video-showcase';

o_video_card.o_video_card_is_player = true;

related_videos.o_video_card_list.o_video_card_list_items[0].o_video_card_link_classes += ' is-playing';

module.exports = {
	...video_showcase,
};
