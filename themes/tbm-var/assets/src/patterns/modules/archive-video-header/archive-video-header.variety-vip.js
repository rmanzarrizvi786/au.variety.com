const clonedeep = require( 'lodash.clonedeep' );

const archive_video_header_prototype = require( './archive-video-header.prototype' );
const archive_video_header = clonedeep( archive_video_header_prototype );

module.exports = {
	...archive_video_header
};
