const clonedeep = require( 'lodash.clonedeep' );

const homepage_vip_tv_film_prototype = require( './homepage-vip-tv-film.prototype' );
const homepage_vip_tv_film = clonedeep( homepage_vip_tv_film_prototype );

const vip_curated_prototype = require( '../vip-curated/vip-curated.exposure' );
const vip_curated = clonedeep( vip_curated_prototype );
vip_curated.c_span = false;

const homepage_vertical_list_prototype = require( '../homepage-vertical-list/homepage-vertical-list.prototype' );
const homepage_vertical_list = clonedeep( homepage_vertical_list_prototype );
const homepage_vertical_list_horizontal = clonedeep( homepage_vertical_list_prototype );

homepage_vip_tv_film.homepage_vip_tv_film_classes += ' u-grid-gap-1@tablet a-cols3@tablet';
homepage_vip_tv_film.homepage_vip_tv_classes = homepage_vip_tv_film.homepage_vip_tv_classes.replace( 'a-cols3@tablet', 'a-cols2@tablet' );
homepage_vip_tv_film.homepage_vip_tv_classes += ' a-cols3@desktop-xl';

vip_curated.vip_curated_classes += ' a-span2@desktop-xl';
vip_curated.o_tease_primary.o_tease_classes = 'lrv-u-flex lrv-u-flex-direction-column lrv-u-padding-b-1  lrv-u-border-b-1 u-border-color-pale-sky-2 u-padding-b-100@desktop-xl';
vip_curated.o_more_link.o_more_link_classes = 'lrv-u-text-align-right u-margin-t-150 lrv-u-padding-tb-075 lrv-u-border-t-1 u-border-color-pale-sky-2';

homepage_vertical_list.vertical_list_classes += ' u-order-n1';

homepage_vertical_list.o_more_from_heading.c_heading.c_heading_text = 'Streaming';

homepage_vertical_list_horizontal.vertical_list_classes += ' u-order-n1';
homepage_vertical_list_horizontal.o_more_from_heading.c_heading.c_heading_text = 'Politics';

homepage_vip_tv_film.homepage_vip_tv_classes = homepage_vip_tv_film.homepage_vip_tv_classes.replace( 'u-padding-b-125 u-padding-b-00@desktop-xl', 'u-margin-t-125 u-margin-t-00@tablet' );

module.exports = {
	...homepage_vip_tv_film,
	vip_curated,
	homepage_vertical_list,
	homepage_vertical_list_horizontal,
};
