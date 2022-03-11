const clonedeep = require( 'lodash.clonedeep' );

const article_header_prototype = require( './article-header.prototype.js' );
const article_header = clonedeep( article_header_prototype );

article_header.o_figure = null;

const featured_video_prototype = require( '../featured-video/featured-video.prototype' );
const featured_video = clonedeep( featured_video_prototype );

module.exports = {
  ...article_header,
  featured_video
};
