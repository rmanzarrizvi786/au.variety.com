const clonedeep = require( 'lodash.clonedeep' );

const a_content_prototype = require( './a-content.prototype.js' );
const a_content = clonedeep( a_content_prototype );

const related_articles_prototype = require( '../../modules/related-articles/related-articles.variety-vip.js' );
const related_articles = clonedeep( related_articles_prototype );

module.exports = {
	...a_content,
	related_articles
};
