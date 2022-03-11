const clonedeep = require( 'lodash.clonedeep' );

const o_slide_prototype = require( './o-slide.prototype.js' );
const o_slide = clonedeep( o_slide_prototype );

module.exports = {
	...o_slide,
};
