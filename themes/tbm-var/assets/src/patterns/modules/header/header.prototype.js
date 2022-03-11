const clonedeep = require( 'lodash.clonedeep' );

const header_sticky_prototype = require( '../header-sticky/header-sticky.prototype' );
const header_sticky = clonedeep( header_sticky_prototype );

const header_main_prototype = require( '../header-main/header-main.prototype.js' );
const header_main = clonedeep( header_main_prototype );

module.exports = {
	'header_classes': 'u-z-index-middle lrv-u-position-relative',
	'header_contents_classes': '',
	'header_sticky': header_sticky,
	header_main,
};
