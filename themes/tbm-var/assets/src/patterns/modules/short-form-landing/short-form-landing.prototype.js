const clonedeep = require( 'lodash.clonedeep' );

const trending_topics_prototype = require( '../trending-topics/trending-topics.short-form.js' );
const trending_topics = clonedeep( trending_topics_prototype );

trending_topics.trending_topics_classes += ' lrv-u-padding-t-050';

const more_from_widget_prototype = require( '../more-from-widget/more-from-widget.big.js' );
const more_from_widget = clonedeep( more_from_widget_prototype );

module.exports = {
	short_form_landing_classes: 'lrv-a-grid lrv-a-cols3@tablet a-cols4@desktop u-grid-gap-3@desktop-xl u-margin-t-300@tablet',
	short_form_landing_wrapper_classes: 'lrv-a-wrapper u-margin-t-350@tablet',
	trending_topics,
	more_from_widget,
};
