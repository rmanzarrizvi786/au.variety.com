const clonedeep = require( 'lodash.clonedeep' );

const o_topic_prototype = require( './o-topic.homepage.js' );
const o_topic = clonedeep( o_topic_prototype );

module.exports = {
	...o_topic
};
