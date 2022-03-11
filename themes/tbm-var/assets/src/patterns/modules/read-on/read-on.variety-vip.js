const clonedeep = require( 'lodash.clonedeep' );

const read_on_prototype = require( './read-on.prototype.js' );
const read_on = clonedeep( read_on_prototype );

module.exports = {
	...read_on
};
