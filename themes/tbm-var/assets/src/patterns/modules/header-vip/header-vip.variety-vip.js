const clonedeep = require( 'lodash.clonedeep' );

const header_vip_prototype = require( './header-vip.prototype.js' );
const header_vip = clonedeep( header_vip_prototype );

module.exports = {
	...header_vip,
};
