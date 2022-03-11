const clonedeep = require( 'lodash.clonedeep' );

const homepage_vip_tv_film_prototype = require( './homepage-vip-tv-film.prototype' );
const homepage_vip_tv_film = clonedeep( homepage_vip_tv_film_prototype );

const vip_curated_prototype = require( '../vip-curated/vip-curated.dirt' );
const vip_curated = clonedeep( vip_curated_prototype );

const homepage_vertical_list_prototype = require( '../homepage-vertical-list/homepage-vertical-list.prototype' );
const homepage_vertical_list = clonedeep( homepage_vertical_list_prototype );

const homepage_vertical_list_horizontal_prototype = require( '../homepage-vertical-list/homepage-vertical-list.horizontal' );
const homepage_vertical_list_horizontal = clonedeep( homepage_vertical_list_horizontal_prototype );

vip_curated.vip_curated_classes += ' a-span2@tablet';

homepage_vertical_list.o_more_from_heading.c_heading.c_heading_text = 'Artisans';
homepage_vertical_list.vertical_list_classes += ' lrv-u-height-100p';

module.exports = {
	...homepage_vip_tv_film,
	vip_curated,
	homepage_vertical_list,
	homepage_vertical_list_horizontal,
};
