const clonedeep = require( 'lodash.clonedeep' );

const article_video_header_prototype = require( './article-video-header.prototype' );
const article_video_header = clonedeep( article_video_header_prototype );

article_video_header.o_video_card.c_heading.c_heading_text = 'Page title';
article_video_header.o_video_card.c_heading.c_heading_url = false;

module.exports = {
	...article_video_header
};
