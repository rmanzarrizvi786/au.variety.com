const clonedeep = require( 'lodash.clonedeep' );

const o_top_story_prototype = require( './o-top-story.prototype.js' );
const o_top_story = clonedeep( o_top_story_prototype );

module.exports = {
	...o_top_story
};
