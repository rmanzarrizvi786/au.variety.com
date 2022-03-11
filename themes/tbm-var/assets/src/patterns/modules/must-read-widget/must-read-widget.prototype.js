const clonedeep = require( 'lodash.clonedeep' );

const o_tease_list_prototype = require( '../../objects/o-tease-list/o-tease-list.prototype' );
const o_tease_list_primary = clonedeep( o_tease_list_prototype );
const o_tease_list_secondary = clonedeep( o_tease_list_prototype );

const o_tease_primary_prototype = require( '../../objects/o-tease/o-tease.must-read-primary' );
const o_tease_secondary_prototype = require( '../../objects/o-tease/o-tease.must-read-secondary' );
const o_tease_primary = clonedeep( o_tease_primary_prototype );
const o_tease_secondary = clonedeep( o_tease_secondary_prototype );

o_tease_primary.c_lazy_image.c_lazy_image_classes = '';

const o_taxonomy_item_sponsored_prototype = require( '../../objects/o-taxonomy-item/o-taxonomy-item.sponsored' );
const o_taxonomy_item_sponsored = clonedeep( o_taxonomy_item_sponsored_prototype );

const o_tease_secondary_sponsored = clonedeep( o_tease_secondary_prototype );
o_tease_secondary_sponsored.c_span = o_taxonomy_item_sponsored.c_span;

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' );
const c_heading = clonedeep( c_heading_prototype );

c_heading.c_heading_classes = 'lrv-u-font-weight-bold lrv-u-font-family-secondary u-font-size-25 u-letter-spacing-009@mobile-max u-line-height-1 lrv-u-text-align-center@mobile-max lrv-u-margin-t-050 u-margin-b-075 u-margin-b-2@tablet';
c_heading.c_heading_text = 'Must Read';

o_tease_list_primary.o_tease_list_items = [
	o_tease_primary,
];

o_tease_list_secondary.o_tease_list_classes += ' u-border-color-brand-secondary-40 u-border-t-1 a-separator-b-1';
o_tease_list_secondary.o_tease_list_item_classes = 'u-border-color-brand-secondary-40';

o_tease_secondary.c_title.c_title_classes = 'a-font-secondary-bold-xs lrv-u-padding-b-025';
o_tease_secondary_sponsored.c_title.c_title_classes = 'a-font-secondary-bold-xs lrv-u-padding-b-025';

o_tease_list_secondary.o_tease_list_items = [
	o_tease_secondary,
	o_tease_secondary,
	o_tease_secondary,
	o_tease_secondary_sponsored,
];

module.exports = {
	must_read_widget_classes: 'u-border-color-picked-bluewood u-padding-lr-00@tablet',
	must_read_widget_header_classes: '',
	o_tease_list_primary,
	o_tease_list_secondary,
	c_heading,
};
