const clonedeep = require( 'lodash.clonedeep' );

const vip_curated_prototype = require( './vip-curated.awards' );
const vip_curated = clonedeep( vip_curated_prototype );

const {
	o_more_from_heading,
	o_tease_primary,
	o_tease_list,
	o_more_link,
} = vip_curated;

o_more_from_heading.c_heading.c_heading_classes = o_more_from_heading.c_heading.c_heading_classes.replace( ' lrv-a-screen-reader-only', '' );
o_more_from_heading.c_heading.c_heading_classes += ' lrv-u-color-white';
o_more_from_heading.c_heading.c_heading_text = 'Events & Parties';

o_tease_primary.c_title.c_title_link_classes = o_tease_primary.c_title.c_title_link_classes.replace( 'lrv-u-color-black', 'lrv-u-color-white' );
o_tease_primary.c_title.c_title_link_classes = o_tease_primary.c_title.c_title_link_classes.replace( 'u-color-brand-accent-80:hover', 'u-color-brand-accent-20:hover' );

o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'u-border-color-brand-secondary-40', 'u-border-color-pale-sky-2' );

o_tease_list.o_tease_list_item_classes = o_tease_list.o_tease_list_item_classes.replace( 'u-border-color-brand-secondary-40', 'u-border-color-pale-sky-2' );

o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'a-space-children-vertical@desktop-xl-max', '' );

o_tease_list.o_tease_list_items.forEach( item => {
	item.c_title.c_title_link_classes = item.c_title.c_title_link_classes.replace( 'lrv-u-color-black', 'lrv-u-color-white' );
	item.c_title.c_title_link_classes = item.c_title.c_title_link_classes.replace( 'u-color-brand-accent-80:hover', 'u-color-brand-accent-20:hover' );
} );

o_more_link.o_more_link_classes += ' u-border-color-pale-sky-2';
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'u-color-pale-sky-2', 'lrv-u-color-white' );
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'u-border-color-brand-secondary-40', 'u-border-color-pale-sky-2' );
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'u-color-brand-accent-80:hover', 'u-color-brand-accent-20:hover' );
o_more_link.c_link.c_link_classes += '  u-color-brand-accent-20:hover u-color-white@tablet';
o_more_link.c_link.c_link_text = 'More Events & Parties';

// Note: overriding classes outside of objects to avoid maze
vip_curated.vip_curated_classes = 'u-border-t-6 u-border-color-pale-sky-2 u-box-shadow-menu u-background-color-picked-bluewood lrv-u-height-100p lrv-u-flex@tablet lrv-u-flex-direction-column u-padding-lr-075@mobile-max lrv-u-padding-lr-1@tablet u-margin-lr-n075@mobile-max a-span2@desktop-xl';

module.exports = {
	...vip_curated
};
