const clonedeep = require( 'lodash.clonedeep' );

const video_carousel_prototype = require( './video-carousel.prototype.js' );
const video_carousel = clonedeep( video_carousel_prototype );

module.exports = {
	...video_carousel,
};
