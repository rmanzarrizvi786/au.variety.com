const clonedeep = require( 'lodash.clonedeep' );

const vip_banner_prototype = require( './vip-banner.prototype' );
const vip_banner = clonedeep( vip_banner_prototype );

vip_banner.vip_banner_classes = 'u-width-300 u-height-300 lrv-u-text-align-center lrv-u-flex lrv-u-align-items-center lrv-u-justify-content-center';

module.exports = {
	...vip_banner,
};
