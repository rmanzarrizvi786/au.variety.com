const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.prototype.js' );
const o_tease = clonedeep( o_tease_prototype );

module.exports = {
	...o_tease
};
