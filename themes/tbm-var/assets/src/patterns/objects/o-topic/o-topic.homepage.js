const clonedeep = require( 'lodash.clonedeep' );

const o_topic_prototype = require( './o-topic.prototype.js' );
const o_topic = clonedeep( o_topic_prototype );

o_topic.o_topic_classes = o_topic.o_topic_classes.replace( 'u-max-width-300@tablet', 'u-max-width-200@tablet' );
o_topic.o_topic_classes += ' lrv-u-margin-b-050';

module.exports = {
	...o_topic
};
