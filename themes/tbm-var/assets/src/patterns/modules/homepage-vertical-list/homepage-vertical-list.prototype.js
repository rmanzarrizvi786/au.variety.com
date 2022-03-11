const clonedeep = require( 'lodash.clonedeep' );

const o_tease_list_prototype = require( '../../objects/o-tease-list/o-tease-list.prototype' );
const o_tease_list_primary = clonedeep( o_tease_list_prototype );
const o_tease_list_secondary = clonedeep( o_tease_list_prototype );

const o_tease_primary_prototype = require( '../../objects/o-tease/o-tease.vertical-list.primary' );
const o_tease_secondary_prototype = require( '../../objects/o-tease/o-tease.vertical-list' );
const o_tease_tertiary_prototype = require( '../../objects/o-tease/o-tease.square' );
const o_tease_primary = clonedeep( o_tease_primary_prototype );
const o_tease_secondary = clonedeep( o_tease_secondary_prototype );
const o_tease_tertiary = clonedeep( o_tease_tertiary_prototype );

const o_more_from_heading = clonedeep( require( '../../objects/o-more-from-heading/o-more-from-heading.homepage' ) );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.blue.homepage' );
const o_more_link = clonedeep( o_more_link_prototype );

o_more_from_heading.c_heading.c_heading_text = 'Politics';
o_more_from_heading.c_v_icon = null;

o_tease_primary.o_tease_classes += ' o-tease--primary';
o_tease_primary.c_title.c_title_classes += ' u-min-height-36em u-max-height-36em a-truncate-ellipsis';

o_tease_secondary.o_tease_classes = o_tease_secondary.o_tease_classes.replace( 'u-padding-b-250@tablet', 'u-padding-b-125@tablet' );
o_tease_secondary.c_title.c_title_classes += ' u-min-height-36em u-max-height-36em a-truncate-ellipsis';

o_tease_list_primary.o_tease_list_items = [
	o_tease_primary,
	o_tease_secondary,
];

o_tease_list_secondary.o_tease_list_classes += ' a-separator-b-1';
o_tease_list_secondary.o_tease_list_item_classes = 'u-border-color-brand-secondary-40';

o_tease_list_secondary.o_tease_list_items = [
	o_tease_tertiary,
	o_tease_tertiary,
	o_tease_tertiary,
];

o_more_link.c_link.c_link_text = 'More Politics';

module.exports = {
	vertical_list_classes: 'u-border-t-6 u-border-color-picked-bluewood u-box-shadow-menu u-padding-lr-075@mobile-max lrv-u-padding-lr-1@tablet lrv-u-background-color-white',
	vertical_list_inner_classes: '',
	vertical_list_header_classes: '',
	o_tease_list_primary,
	o_tease_list_secondary,
	o_more_from_heading,
	o_more_link,
};
