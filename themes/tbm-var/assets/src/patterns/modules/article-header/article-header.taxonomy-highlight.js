const clonedeep = require( 'lodash.clonedeep' );

const article_header = require( './article-header.prototype.js' );
const c_taxonomy_highlight = require( '../../components/c-taxonomy-highlight/c-taxonomy-highlight.prototype' );

module.exports = {
	...article_header,
	c_taxonomy_highlight
};
