const clonedeep = require( 'lodash.clonedeep' );

const vip_curated_prototype = require( '../homepage-horizontal-block/homepage-horizontal-block.prototype' );
const vip_curated = clonedeep( vip_curated_prototype );
vip_curated.c_span = false;

module.exports = {
	homepage_vip_classes: 'lrv-a-wrapper lrv-a-grid u-grid-gap-0 u-grid-gap-1@desktop-xl',
	vip_curated,
};
