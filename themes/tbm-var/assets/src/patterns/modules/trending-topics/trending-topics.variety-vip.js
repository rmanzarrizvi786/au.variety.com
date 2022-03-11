const clonedeep = require( 'lodash.clonedeep' );

const trending_topics_prototype = require( './trending-topics.prototype.js' );
const trending_topics = clonedeep( trending_topics_prototype );

module.exports = {
	...trending_topics,
};
