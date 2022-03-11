const clonedeep = require( 'lodash.clonedeep' );

const video_showcase_prototype = require( './video-showcase.prototype' );
const video_showcase = clonedeep( video_showcase_prototype );

const o_video_card = require( '../../objects/o-video-card/o-video-card.playlist.js' );

video_showcase.video_header_videos_classes = video_showcase.video_header_videos_classes.replace( 'u-padding-b-125@tablet', 'u-padding-b-2@tablet' );

video_showcase.o_video_card = clonedeep( o_video_card );

video_showcase.related_videos.related_videos_classes += ' lrv-u-display-none';

module.exports = {
	...video_showcase,
};
