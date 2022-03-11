const clonedeep = require( 'lodash.clonedeep' );

const o_tease_list_prototype = require( './o-tease-list.prototype.js' );
const o_tease_list = clonedeep( o_tease_list_prototype );

module.exports = {
	...o_tease_list
};
