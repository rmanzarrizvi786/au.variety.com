const clonedeep = require( 'lodash.clonedeep' );

const vip_curated_prototype = require( '../vip-curated/vip-curated.prototype' );
const vip_curated = clonedeep( vip_curated_prototype );
vip_curated.c_span = false;

const homepage_vertical_list_prototype = require( '../homepage-vertical-list/homepage-vertical-list.prototype' );
const homepage_vertical_list = clonedeep( homepage_vertical_list_prototype );

const homepage_vertical_list_horizontal_prototype = require( '../homepage-vertical-list/homepage-vertical-list.horizontal' );
const homepage_vertical_list_horizontal = clonedeep( homepage_vertical_list_horizontal_prototype );

vip_curated.vip_curated_classes += ' a-span2@tablet';

homepage_vertical_list.o_more_from_heading.c_heading.c_heading_text = 'TV';
homepage_vertical_list.vertical_list_classes += ' lrv-u-height-100p';

module.exports = {
	homepage_vip_tv_film_classes: 'lrv-a-wrapper lrv-a-grid u-grid-gap-0 u-grid-gap-1@desktop-xl a-cols4@desktop-xl',
	homepage_vip_tv_classes: 'lrv-a-grid a-cols3@tablet a-span2@tablet a-span3@desktop-xl u-padding-b-125 u-padding-b-00@desktop-xl',
	vip_curated,
	homepage_vertical_list,
	homepage_vertical_list_horizontal,
};
