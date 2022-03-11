const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.homepage' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const o_tease_list_prototype = require( '../../objects/o-tease-list/o-tease-list.prototype' );
const o_tease_list = clonedeep( o_tease_list_prototype );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.blue.homepage' );
const o_more_link = clonedeep( o_more_link_prototype );

const o_tease_primary_prototype = require( '../../objects/o-tease/o-tease.voices.primary' );
const o_tease_primary = clonedeep( o_tease_primary_prototype );

const o_tease_secondary_prototype = require( '../../objects/o-tease/o-tease.voices.secondary' );
const o_tease_secondary = clonedeep( o_tease_secondary_prototype );

o_more_from_heading.c_heading.c_heading_classes += ' lrv-u-color-white';
o_more_from_heading.c_heading.c_heading_text = 'What To Buy';

// o-more-link.blue.homepage.dark
o_more_link.o_more_link_classes = o_more_link.o_more_link_classes.replace( 'u-border-color-brand-secondary-40', 'u-border-color-pale-sky-2' );
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'u-color-pale-sky-2', 'lrv-u-color-white u-color-brand-accent-20:hover' );
o_more_link.c_link.c_link_text = 'More';
// end o-more-link.blue.homepage.dark

o_tease_list.o_tease_list_classes += ' lrv-a-grid u-grid-gap-0 a-cols4@tablet a-separator-r-1@tablet a-separator-spacing--r-1@tablet u-padding-b-1@tablet a-separator-b-1@mobile-max';
o_tease_list.o_tease_list_item_classes += ' u-border-color-pale-sky-2 lrv-u-height-100p';

o_tease_list.o_tease_list_items = [
	o_tease_primary,
	o_tease_secondary,
	o_tease_secondary,
	o_tease_secondary,
];

module.exports = {
	homepage_voices_wrapper_classes: 'lrv-a-wrapper a-wrapper-padding-unset@mobile-max',
	homepage_voices_classes: 'u-background-color-picked-bluewood u-border-t-6 u-border-color-pale-sky-2 u-box-shadow-menu u-padding-lr-075@mobile-max lrv-u-padding-lr-1@tablet',
	o_more_from_heading,
	o_tease_list,
	o_more_link,
	c_span_title: null,
	c_span_subtitle: null,
	c_link: null,
	c_footer_tagline: null,
};
