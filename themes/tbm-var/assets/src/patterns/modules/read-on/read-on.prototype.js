const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

const read_on_item_prototype = require( '../../objects/o-read-on-item/o-read-on-item.prototype.js' );
const read_on_item = clonedeep( read_on_item_prototype );

c_heading.c_heading_classes = 'lrv-u-text-align-center@mobile-max lrv-u-margin-b-1 u-margin-b-2@tablet u-font-size-23';
c_heading.c_heading_text = 'Read on to learn about:';

read_on_item.o_read_on_item_classes = 'lrv-a-grid-item';

const read_on_item_secondary = clonedeep( read_on_item );
read_on_item_secondary.c_span.c_span_text = '2';

const read_on_item_tertiary = clonedeep( read_on_item );
read_on_item_tertiary.c_span.c_span_text = '3';

const read_on_items = [
	read_on_item,
	read_on_item_secondary,
	read_on_item_tertiary,
];

module.exports = {
	read_on_classes: 'lrv-u-margin-lr-auto u-max-width-618',
	read_on_inner_classes: 'lrv-a-grid lrv-a-cols3@tablet lrv-u-margin-lr-auto u-grid-gap-125 u-max-width-300@mobile-max',
	c_heading,
	read_on_items
};
