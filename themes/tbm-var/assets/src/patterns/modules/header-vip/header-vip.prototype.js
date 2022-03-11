const clonedeep = require( 'lodash.clonedeep' );

const header_sticky_prototype = require( '../header-sticky/header-sticky.variety-vip.js' );
const header_sticky = clonedeep( header_sticky_prototype );

module.exports = {
	header_sticky,
};
