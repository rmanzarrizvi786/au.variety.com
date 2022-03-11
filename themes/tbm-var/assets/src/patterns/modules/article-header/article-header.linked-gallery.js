const clonedeep = require( 'lodash.clonedeep' );

const article_header_prototype = require( './article-header.prototype.js' );
const article_header = clonedeep( article_header_prototype );

article_header.o_figure = false;

const linked_gallery_prototype = require( '../linked-gallery/linked-gallery.prototype' );
const linked_gallery = clonedeep( linked_gallery_prototype );

module.exports = {
  ...article_header,
  linked_gallery
};
