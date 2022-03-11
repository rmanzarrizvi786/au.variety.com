const clonedeep = require( 'lodash.clonedeep' );

const homepage_vertical_list_prototype = require( './homepage-vertical-list.prototype' );
const homepage_vertical_list = clonedeep( homepage_vertical_list_prototype );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.fancy' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const o_tease_primary_prototype = require( '../../objects/o-tease/o-tease.special.primary' );
const o_tease_primary = clonedeep( o_tease_primary_prototype );

o_tease_primary.c_span = false;
o_tease_primary.c_link = false;
o_tease_primary.c_timestamp = false;

const o_tease_tertiary_prototype = require( '../../objects/o-tease/o-tease.square.big' );
const o_tease_tertiary = clonedeep( o_tease_tertiary_prototype );

o_tease_tertiary.c_title.c_title_classes += ' lrv-u-font-family-secondary';
o_tease_tertiary.c_title.c_title_link_classes = 'lrv-u-color-white lrv-u-display-block u-color-brand-secondary-50:hover';

const {
	o_tease_list_primary,
	o_tease_list_secondary,
	o_more_link,
} = homepage_vertical_list;

const o_tease_secondary = clonedeep( o_tease_list_primary.o_tease_list_items[1] );

o_tease_secondary.o_tease_classes += ' u-margin-t-125@tablet';
o_tease_secondary.c_lazy_image.c_lazy_image_crop_class = 'a-crop-3x2';
o_tease_secondary.c_title.c_title_classes += ' u-font-family-primary@tablet u-font-size-21@tablet u-font-weight-normal@tablet';
o_tease_secondary.c_title.c_title_classes = o_tease_secondary.c_title.c_title_classes.replace( 'u-max-height-36em', '' );
o_tease_secondary.c_title.c_title_classes = o_tease_secondary.c_title.c_title_classes.replace( 'u-min-height-36em', '' );

o_tease_secondary.c_title.c_title_link_classes = 'lrv-u-color-white lrv-u-display-block u-color-brand-secondary-50:hover u-color-brand-accent-20:hover@tablet';

o_tease_secondary.c_title.c_title_classes += ' u-font-size-24@desktop-xl';

o_tease_list_primary.o_tease_list_items[0] = o_tease_primary;
o_tease_list_primary.o_tease_list_items[1] = o_tease_secondary;

o_tease_list_primary.o_tease_list_items[0].o_tease_classes = o_tease_list_primary.o_tease_list_items[1].o_tease_classes.replace( 'u-border-color-brand-secondary-40', 'u-border-color-pale-sky-2' );

o_tease_list_primary.o_tease_list_items[1].o_tease_classes = o_tease_list_primary.o_tease_list_items[1].o_tease_classes.replace( 'u-border-color-brand-secondary-40', 'u-border-color-pale-sky-2' );


o_tease_list_primary.o_tease_list_items[0].o_tease_classes = 'lrv-u-flex lrv-u-flex-direction-column u-padding-b-125 u-padding-t-075 u-padding-t-00@tablet u-padding-b-125@tablet lrv-u-border-b-1 u-border-color-pale-sky-2 u-padding-b-125@tablet u-margin-t-125@tablet';


o_tease_list_secondary.o_tease_list_items = [
	o_tease_tertiary,
	o_tease_tertiary,
	o_tease_tertiary,
];

o_more_link.c_link.c_link_text = 'More Emmys';
o_more_link.c_link.c_link_classes += ' u-color-brand-accent-20:hover@tablet';
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'u-color-pale-sky-2 ', 'lrv-u-color-white ' );
o_more_link.o_more_link_classes = 'lrv-u-text-align-right u-margin-t-150 lrv-u-padding-tb-075 lrv-u-border-t-1 u-border-color-pale-sky-2';

// Note: this is such a maze of .replace and other overrides, so overwriting the entire value.
// the classes that do not change between variants should be in twig
homepage_vertical_list.vertical_list_classes = 'lrv-u-flex@tablet lrv-u-flex-direction-column lrv-u-height-100p u-border-t-6@mobile-max u-border-color-pale-sky-2 u-box-shadow-menu u-padding-lr-075@mobile-max lrv-u-padding-lr-1@tablet u-background-color-picked-bluewood u-margin-t-125 u-margin-t-00@tablet';

homepage_vertical_list.o_tease_list_secondary.o_tease_list_item_classes = 'u-border-color-pale-sky-2';

homepage_vertical_list.vertical_list_header_classes += ' a-border-fancy u-margin-b-075';

module.exports = {
	...homepage_vertical_list,
	o_more_from_heading,
};
