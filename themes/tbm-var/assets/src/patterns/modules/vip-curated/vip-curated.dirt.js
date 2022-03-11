const clonedeep = require( 'lodash.clonedeep' );

const vip_curated_prototype = require( './vip-curated.prototype' );
const vip_curated = clonedeep( vip_curated_prototype );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' );

const o_span_group_prototype = require( '@penskemediacorp/larva-patterns/objects/o-span-group/o-span-group.prototype' );
const o_span_group = clonedeep( o_span_group_prototype );
const o_span_group_secondary = clonedeep( o_span_group_prototype );

const dirtData = [ '$3.995 million', 'Soho, New York City, N.Y.' ];

const {
	o_more_from_heading,
	o_tease_primary,
	o_tease_list,
	o_more_link,
} = vip_curated;

vip_curated.vip_curated_classes = vip_curated.vip_curated_classes.replace( 'u-border-color-vip-brand-primary', 'u-border-color-picked-bluewood' );
vip_curated.vip_curated_classes = vip_curated.vip_curated_classes.replace( 'u-padding-b-150', '' );
vip_curated.vip_curated_classes += ' u-background-color-picked-bluewood@tablet u-border-color-pale-sky-2@tablet';

vip_curated.vip_curated_stories_classes = vip_curated.vip_curated_stories_classes.replace( 'u-margin-t-1@tablet', '' );

o_more_from_heading.c_heading.c_heading_classes = o_more_from_heading.c_heading.c_heading_classes.replace( 'lrv-a-screen-reader-only', '' );
o_more_from_heading.c_heading.c_heading_classes +=  ' u-color-white@tablet';
o_more_from_heading.c_heading.c_heading_text = 'Dirt';
o_more_from_heading.c_v_icon = null;

o_span_group.o_span_group_items = [];
o_span_group_secondary.o_span_group_items = [];
o_span_group.o_span_group_classes = 'a-separator-r-1@tablet lrv-a-space-children-horizontal@tablet a-space-children--075';

for ( item of dirtData ) {
	let c_span = clonedeep( c_span_prototype );

	c_span.c_span_text = item;
	c_span.c_span_classes = 'lrv-u-font-family-secondary lrv-u-font-size-12 u-line-height-120 u-color-white@tablet u-line-height-1@tablet u-padding-r-075@tablet lrv-u-padding-b-025 ';

	o_span_group.o_span_group_items.push( c_span );
	o_span_group_secondary.o_span_group_items.push( c_span );
}

const o_span_group_tease_primary = clonedeep( o_span_group );
o_span_group_tease_primary.o_span_group_items[1].c_span_classes += ' u-padding-l-075';

o_tease_primary.o_tease_classes += ' u-border-color-pale-sky-2@tablet u-padding-b-075@tablet';
o_tease_primary.c_title.c_title_classes = o_tease_primary.c_title.c_title_classes.replace( 'lrv-u-font-family-primary', 'u-font-family-primary@tablet' );
o_tease_primary.c_title.c_title_classes = o_tease_primary.c_title.c_title_classes.replace( 'lrv-u-font-weight-normal', 'lrv-u-font-weight-bold' );
o_tease_primary.c_title.c_title_classes = o_tease_primary.c_title.c_title_classes.replace( 'u-font-size-22', 'u-font-size-15' );
o_tease_primary.c_title.c_title_classes += ' lrv-u-font-family-secondary u-margin-t-050@tablet u-font-weight-normal@tablet lrv-u-padding-b-025';
o_tease_primary.c_title.c_title_link_classes += ' u-color-brand-accent-80:hover u-color-white@tablet u-color-brand-accent-20:hover@tablet';


o_tease_primary.o_span_group = o_span_group_tease_primary;

o_tease_primary.c_lazy_image.c_lazy_image_classes = o_tease_primary.c_lazy_image.c_lazy_image_classes.replace( 'u-margin-lr-n050@mobile-max', '' );

o_span_group_secondary.o_span_group_classes = 'lrv-u-flex lrv-u-flex-direction-column@mobile-max a-separator-r-1@tablet lrv-a-space-children-horizontal@tablet a-space-children--075';

o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'u-margin-t-025', '' );
o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'lrv-a-space-children-horizontal@tablet', '' );
o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'u-padding-b-150@tablet', 'u-padding-b-050@tablet' );
o_tease_list.o_tease_list_classes += ' u-border-color-pale-sky-2@tablet u-padding-b-175@desktop-xl';
o_tease_list.o_tease_list_item_classes += ' u-border-color-pale-sky-2@tablet';

for ( item of o_tease_list.o_tease_list_items ) {
	const {
		c_title,
		c_lazy_image,
	} = item;

	item.o_tease_classes += ' u-border-color-pale-sky-2@tablet u-padding-b-00@desktop-xl';
	item.o_span_group = o_span_group_secondary;
	item.o_tease_secondary_classes = item.o_tease_secondary_classes.replace( 'u-order-n1@tablet', 'u-order-n1' );
	item.o_tease_secondary_classes = item.o_tease_secondary_classes.replace( 'u-width-44p@mobile-max', 'u-width-100@mobile-max' );
	item.o_tease_secondary_classes += ' lrv-u-margin-r-1';

	c_title.c_title_classes += ' lrv-u-margin-b-050';
	c_title.c_title_classes = c_title.c_title_classes.replace( 'u-font-size-15', 'lrv-u-font-size-14' );
	c_title.c_title_link_classes = c_title.c_title_link_classes.replace( 'u-color-brand-secondary-50:hover', 'u-color-brand-accent-80:hover' );
	c_title.c_title_link_classes += ' u-color-white@tablet u-color-brand-accent-20:hover@tablet';

	c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-1x1 a-crop-16x9@tablet';
}

o_more_link.o_more_link_classes += ' u-border-color-pale-sky-2@tablet';
o_more_link.c_link.c_link_classes += '  u-color-brand-accent-20:hover u-color-white@tablet';
o_more_link.c_link.c_link_text = 'More Dirt';


module.exports = {
	...vip_curated,
};
