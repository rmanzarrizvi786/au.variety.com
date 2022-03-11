const clonedeep = require( 'lodash.clonedeep' );

const latest_from_prototype = require( './latest-from.prototype.js' );
const latest_from = clonedeep( latest_from_prototype );

module.exports = {
	...latest_from
};
