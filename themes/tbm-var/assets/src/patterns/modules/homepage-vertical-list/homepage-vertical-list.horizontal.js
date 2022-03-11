const clonedeep = require( 'lodash.clonedeep' );

const homepage_vertical_list_prototype = require( './homepage-vertical-list.prototype' );
const homepage_vertical_list = clonedeep( homepage_vertical_list_prototype );

const {
	o_more_from_heading,
	o_tease_list_primary,
	o_tease_list_secondary,
	o_more_link,
} = homepage_vertical_list;

const o_tease_primary = clonedeep( o_tease_list_primary.o_tease_list_items[0] );
const o_tease_secondary = clonedeep( o_tease_list_primary.o_tease_list_items.pop() );
const o_tease_tertiary = clonedeep( o_tease_list_secondary.o_tease_list_items.pop() );

// Note: All of this is a total mess, and needs to be refactored to remove the array methods
// and the different tease variants, and classes added to the Twig.

o_tease_secondary.o_tease_secondary_classes += ' lrv-u-display-none u-display-block@desktop-xl';

o_more_from_heading.c_heading.c_heading_text = 'Tech';

o_tease_secondary.o_tease_classes = 'lrv-u-flex u-flex-direction-column@desktop-xl u-padding-b-125 u-padding-t-075 u-border-color-brand-secondary-40 o-tease--secondary';

o_tease_secondary.o_tease_secondary_classes = o_tease_secondary.o_tease_secondary_classes.replace( 'u-width-44p@mobile-max', 'u-width-44p@desktop-xl-max' );
o_tease_secondary.o_tease_secondary_classes = o_tease_secondary.o_tease_secondary_classes.replace( 'u-margin-r-125@tablet', 'u-margin-r-125@desktop-xl' );
o_tease_secondary.o_tease_secondary_classes += ' u-width-45p@tablet lrv-u-margin-r-1@tablet u-width-100p@desktop-xl';

o_tease_tertiary.o_tease_classes = 'u-padding-t-075 lrv-u-flex u-padding-b-125 u-padding-b-1@tablet u-padding-t-1@desktop-xl u-padding-b-150@desktop-xl';

const o_tease_tertiary_alt = clonedeep( o_tease_tertiary );

o_tease_tertiary_alt.o_tease_classes = 'lrv-u-flex u-padding-b-125 u-padding-t-075 u-padding-b-1@tablet u-padding-t-1@desktop-xl u-padding-b-150@desktop-xl u-border-color-brand-secondary-40 o-tease--tertiary-alt';

o_tease_tertiary.o_tease_classes += ' o-tease--tertiary';

o_tease_list_primary.o_tease_list_classes = 'lrv-a-unstyle-list lrv-u-padding-r-1@tablet u-padding-r-00@desktop-xl u-border-r-1@tablet u-border-r-0@desktop-xl u-border-color-brand-secondary-40 u-width-44p@tablet u-width-100p@desktop-xl';

o_tease_list_secondary.o_tease_list_classes += ' lrv-a-unstyle-list a-separator-b-1 u-padding-l-1@tablet u-padding-lr-00@desktop-xl lrv-u-flex-grow-1';

o_tease_primary.o_tease_classes = 'lrv-u-flex lrv-u-flex-direction-column lrv-u-padding-b-1  lrv-u-border-b-1 u-border-b-0@desktop u-border-b-1@desktop-xl u-border-color-brand-secondary-40 u-padding-b-125@tablet o-tease--primary';

o_tease_tertiary.c_lazy_image = false;
o_tease_tertiary_alt.c_lazy_image = false;

o_tease_primary.c_title.c_title_classes = 'a-font-secondary-bold-s u-margin-r-050@mobile-max u-margin-t-050 u-min-height-36em@desktop-xl u-max-height-36em';

o_tease_tertiary.c_title.c_title_classes += ' u-min-height-36em@desktop-xl u-max-height-36em u-padding-t-075@tablet u-padding-b-1@tablet u-padding-t-1@desktop-xl u-padding-b-150@desktop-xl';
o_tease_tertiary_alt.c_title.c_title_classes += ' u-min-height-36em@desktop-xl u-max-height-36em u-padding-t-075@tablet u-padding-b-1@tablet u-padding-t-1@desktop-xl u-padding-b-150@desktop-xl';

o_tease_list_primary.o_tease_list_items = [
	o_tease_primary
];

o_tease_list_secondary.o_tease_list_items = [
	o_tease_secondary,
	o_tease_tertiary_alt,
	o_tease_tertiary,
	o_tease_tertiary,
];

o_more_link.c_link.c_link_text = 'More Tech';

homepage_vertical_list.vertical_list_inner_classes = 'lrv-u-flex@tablet u-flex-direction-column@desktop-xl';

homepage_vertical_list.vertical_list_classes += ' homepage-vertical-list--horizontal';

module.exports = {
	...homepage_vertical_list,
};
