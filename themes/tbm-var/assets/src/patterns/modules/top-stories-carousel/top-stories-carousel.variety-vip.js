const clonedeep = require( 'lodash.clonedeep' );

const top_stories_carousel_prototype = require( './top-stories-carousel.prototype.js' );
const top_stories_carousel = clonedeep( top_stories_carousel_prototype );

module.exports = {
	...top_stories_carousel
};
