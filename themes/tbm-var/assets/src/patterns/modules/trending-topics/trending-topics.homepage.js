const clonedeep = require( 'lodash.clonedeep' );

const trending_topics_prototype = require( './trending-topics.prototype.js' );
const trending_topics = clonedeep( trending_topics_prototype );

const o_topic_prototype = require( '../../objects/o-topic/o-topic.homepage.js' );
const o_topic = clonedeep( o_topic_prototype );

trending_topics.trending_topics_classes = trending_topics.trending_topics_classes.replace( 'u-border-t-6@mobile-max', '' );

trending_topics.trending_topics = [
	o_topic,	
	o_topic,	
	o_topic,	
	o_topic,	
];

trending_topics.trending_topics_classes += ' lrv-u-margin-lr-auto u-max-width-830 u-margin-t-275@tablet';

module.exports = {
	trending_topics_inner_classes: 'lrv-u-flex lrv-u-flex-direction-column@mobile-max lrv-u-justify-content-space-between',
	...trending_topics
};
