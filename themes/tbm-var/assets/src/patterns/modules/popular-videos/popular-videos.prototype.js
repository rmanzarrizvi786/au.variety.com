const clonedeep = require( 'lodash.clonedeep' );

const o_popular_videos_prototype = require( '../../objects/o-popular-videos/o-popular-videos.prototype.js' );
const o_popular_videos = clonedeep( o_popular_videos_prototype );
const o_popular_videos_first = clonedeep( o_popular_videos_prototype );
const o_popular_videos_last = clonedeep( o_popular_videos_prototype );

o_popular_videos_first.o_popular_videos_classes = o_popular_videos_first.o_popular_videos_classes.replace( 'u-margin-tb-125', 'u-padding-t-125' );
o_popular_videos_last.o_popular_videos_classes = o_popular_videos_last.o_popular_videos_classes.replace( 'u-margin-tb-125', 'u-padding-b-125' );

const popular_videos_items = [
	o_popular_videos_first,
	o_popular_videos,
	o_popular_videos,
	o_popular_videos,
	o_popular_videos_last,
];

module.exports = {
	popular_videos_classes: '',
	popular_videos_items,
};
