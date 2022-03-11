const clonedeep = require( 'lodash.clonedeep' );

const article_header = require( './article-header.prototype.js' );
const c_sponsored = require( '../../components/c-sponsored/c-sponsored.prototype' );

module.exports = {
	...article_header,
	c_sponsored
};
