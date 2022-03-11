const clonedeep = require( 'lodash.clonedeep' );

const o_top_story_prototype = require( '../../objects/o-top-story/o-top-story.prototype.js' );
const o_top_story = clonedeep( o_top_story_prototype );

o_top_story.o_top_story_classes += ' u-margin-lr-n050';

module.exports = {
	special_report_landing_header_classes: 'lrv-a-wrapper u-border-b-6@mobile-max u-border-color-dusty-grey',
	o_top_story
};
