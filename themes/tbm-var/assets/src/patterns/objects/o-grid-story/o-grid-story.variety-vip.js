const clonedeep = require( 'lodash.clonedeep' );

const o_grid_story_prototype = require( './o-grid-story.prototype.js' );
const o_grid_story = clonedeep( o_grid_story_prototype );

module.exports = {
	...o_grid_story
};
