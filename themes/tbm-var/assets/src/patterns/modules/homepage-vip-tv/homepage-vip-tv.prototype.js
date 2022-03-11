const clonedeep = require( 'lodash.clonedeep' );

const vip_curated_prototype = require( '../vip-curated/vip-curated.prototype' );
const vip_curated = clonedeep( vip_curated_prototype );

const homepage_vertical_list_prototype = require( '../homepage-vertical-list/homepage-vertical-list.prototype' );
const homepage_vertical_list = clonedeep( homepage_vertical_list_prototype );

vip_curated.vip_curated_classes += ' a-span2@tablet';

homepage_vertical_list.o_more_from_heading.c_heading.c_heading_text = 'TV';

module.exports = {
	homepage_vip_tv_classes: 'lrv-u-height-100p lrv-a-grid lrv-a-wrapper a-cols3@tablet',
	vip_curated,
	homepage_vertical_list,
};
