const clonedeep = require( 'lodash.clonedeep' );

const view_full_prototype = require( './view-full.prototype.js' );
const view_full = clonedeep( view_full_prototype );

module.exports = {
	...view_full
};
