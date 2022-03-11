const clonedeep = require( 'lodash.clonedeep' );

const header_prototype = require( './header.prototype' );
const header = clonedeep( header_prototype );

const header_main_prototype = require( '../header-main/header-main.homepage' );
const header_main = clonedeep( header_main_prototype );

module.exports = {
	...header,
	header_main,
};
