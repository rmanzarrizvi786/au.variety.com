const clonedeep = require( 'lodash.clonedeep' );

const o_tease_news_prototype = require( './o-tease-news.prototype' );
const o_tease_news = clonedeep( o_tease_news_prototype );

module.exports = {
	...o_tease_news,
	video_permalink_url: '#post',
	is_video: true,
};
