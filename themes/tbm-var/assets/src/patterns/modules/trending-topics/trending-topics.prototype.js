const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

const o_topic_prototype = require( '../../objects/o-topic/o-topic.prototype.js' );
const o_topic = clonedeep( o_topic_prototype );

c_heading.c_heading_classes = 'lrv-u-font-family-primary lrv-u-text-align-center@mobile-max u-text-transform-uppercase@mobile-max u-font-size-30 u-font-size-32@tablet u-font-weight-medium u-letter-spacing-040@mobile-max lrv-u-margin-b-050 u-font-family-secondary@tablet u-font-weight-bold@tablet u-margin-b-125@tablet u-margin-t-025@mobile-max';
c_heading.c_heading_text = 'Trending Topics';

o_topic.o_topic_classes += ' u-margin-b-062';

const trending_topics = [
	o_topic,
	o_topic,
	o_topic,
];

module.exports = {
	trending_topics_classes: 'u-border-t-6@mobile-max u-border-color-brand-secondary-50 u-padding-lr-2@mobile-max',
	c_heading,
	trending_topics,
};
