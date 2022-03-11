const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( '../o-tease-news/o-tease-news.prototype' );
const o_tease = clonedeep( o_tease_prototype );
const o_tease_video_prototype = require( '../o-tease-news/o-tease-news.video' );
const o_tease_video = clonedeep( o_tease_video_prototype );
const o_tease_sponsored_prototype = require( '../o-tease-news/o-tease-news.sponsored' );
const o_tease_sponsored = clonedeep( o_tease_sponsored_prototype );

module.exports = {
	o_tease_list_classes: 'lrv-a-unstyle-list',
	o_tease_list_item_classes: 'u-padding-b-125 lrv-u-border-b-1 u-border-color-brand-secondary-40 u-margin-b-075 u-margin-b-125@tablet',
	o_tease_list_id_attr: null,
	o_tease_list_items: [
		o_tease,
		o_tease_sponsored,
		o_tease_video,
		o_tease,
	]
}
