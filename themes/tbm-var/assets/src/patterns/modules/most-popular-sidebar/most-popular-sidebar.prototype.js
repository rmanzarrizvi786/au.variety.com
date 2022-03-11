const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.homepage' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const o_tease_list_prototype = require( '../../objects/o-tease-list/o-tease-list.prototype' );
const o_tease_list = clonedeep( o_tease_list_prototype );

const o_tease_prototype = require( '../../objects/o-tease/o-tease.popular' );
const o_tease = clonedeep( o_tease_prototype );


// Remove vertical
o_tease.c_span = null;

// Fix margin
o_tease.c_title.c_title_classes += ' u-margin-t-075';


const o_tease_sponsored_prototype = require( '../../objects/o-tease/o-tease.popular-sponsored' );
const o_tease_sponsored = clonedeep( o_tease_sponsored_prototype );

const cxense_subscribe_widget = clonedeep( require( '../cxense-widget/cxense-widget.prototype' ) );

o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'lrv-u-margin-b-050', '' );
o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'u-margin-b-125@tablet', '' );

o_more_from_heading.o_more_from_heading_classes += ' u-border-b-1 u-border-color-brand-secondary-40';
o_more_from_heading.c_heading.c_heading_text = 'Most Popular';

o_tease_list.o_tease_list_item_classes += ' u-border-b-1 u-border-color-brand-secondary-40 lrv-u-padding-b-050';

o_tease_list.o_tease_list_items = [
	o_tease,
	o_tease,
	o_tease,
	o_tease_sponsored,
	o_tease,
	o_tease,
	o_tease,
	o_tease,
];

cxense_subscribe_widget.cxense_id_attr = 'cx-module-300x250';

module.exports = {
	most_popular_sidebar_classes: 'u-max-height-550@tablet u-max-height-650@desktop-xl ',
	o_more_from_heading,
	o_tease_list,
	cxense_subscribe_widget
};
