const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.popular' );
const o_tease = clonedeep( o_tease_prototype );

const o_taxonomy_item_prototype = require( '../o-taxonomy-item/o-taxonomy-item.sponsored' );
const o_taxonomy_item = clonedeep( o_taxonomy_item_prototype );

o_taxonomy_item.c_span.c_span_classes = o_taxonomy_item.c_span.c_span_classes.replace( 'lrv-u-padding-tb-050', 'lrv-u-padding-t-050 lrv-u-padding-b-025' );

module.exports = {
	...o_tease,
	c_span: o_taxonomy_item.c_span,
};
