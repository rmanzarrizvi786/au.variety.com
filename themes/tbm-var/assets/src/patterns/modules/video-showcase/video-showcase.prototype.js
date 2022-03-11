const clonedeep = require( 'lodash.clonedeep' );

const o_video_card_prototype = require( '../../objects/o-video-card/o-video-card.prototype.js' );
const o_video_card = clonedeep( o_video_card_prototype );

const related_videos_prototype = require( '../related-videos/related-videos.prototype.js' );
const related_videos = clonedeep( related_videos_prototype );

o_video_card.o_video_card_classes += ' lrv-u-padding-b-050 lrv-u-border-b-1 u-border-color-pale-sky-2 lrv-u-margin-lr-auto';
o_video_card.o_video_card_link_classes += ' lrv-u-margin-r-1';

related_videos.related_videos_classes = related_videos.related_videos_classes.replace( 'u-max-width-320@tablet', 'u-max-width-330@tablet u-max-width-360@desktop-xl lrv-u-flex-shrink-0' );
related_videos.related_videos_classes = related_videos.related_videos_classes.replace( 'u-max-width-320@tablet', 'u-max-width-330@tablet u-max-width-360@desktop-xl lrv-u-flex-shrink-0' );

module.exports = {
	video_header_videos_classes: 'lrv-u-flex@tablet lrv-u-justify-content-space-between u-margin-t-2@tablet u-padding-b-125@tablet',
	video_header_single_player_classes: 'lrv-u-width-100p lrv-u-margin-r-1',
	o_video_card,
	related_videos,
};
