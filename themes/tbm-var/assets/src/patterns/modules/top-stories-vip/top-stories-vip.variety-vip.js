const clonedeep = require( 'lodash.clonedeep' );

const top_stories_prototype = require( './top-stories-vip.prototype' );
const top_stories = clonedeep( top_stories_prototype );

module.exports = {
	...top_stories
};
