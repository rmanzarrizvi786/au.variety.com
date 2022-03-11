const clonedeep = require( 'lodash.clonedeep' );

const o_taxonomy_item_prototype = require( './o-taxonomy-item.prototype' );
const o_taxonomy_item = clonedeep( o_taxonomy_item_prototype );

o_taxonomy_item.c_span.c_span_url = null;
o_taxonomy_item.c_span.c_span_classes += ' lrv-u-padding-tb-050 u-colors-map-sponsored-90';
o_taxonomy_item.c_span.c_span_text = 'Sponsored';

module.exports = {
	...o_taxonomy_item,
};
