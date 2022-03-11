const clonedeep = require( 'lodash.clonedeep' );

const o_title_prototype = require( './o-title.prototype.js' );
const o_title = clonedeep( o_title_prototype );

o_title.c_heading.c_heading_classes = 'a-font-primary-regular-2xl';

module.exports = {
	...o_title
};
