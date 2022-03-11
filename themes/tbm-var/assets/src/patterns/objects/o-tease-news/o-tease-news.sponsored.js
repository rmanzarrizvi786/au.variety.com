const clonedeep = require( 'lodash.clonedeep' );

const o_tease_news_prototype = require( './o-tease-news.prototype' );
const o_tease_news = clonedeep( o_tease_news_prototype );

const o_taxonomy_item_prototype = require( '../o-taxonomy-item/o-taxonomy-item.sponsored' );
const o_taxonomy_item = clonedeep( o_taxonomy_item_prototype );

o_taxonomy_item.c_span.c_span_classes = o_taxonomy_item.c_span.c_span_classes.replace( 'lrv-u-margin-b-025', '' );
o_taxonomy_item.c_span.c_span_classes = o_taxonomy_item.c_span.c_span_classes.replace( 'lrv-u-padding-tb-050', '' );

module.exports = {
	...o_tease_news,
	c_timestamp: false,
	o_taxonomy_item,
};
