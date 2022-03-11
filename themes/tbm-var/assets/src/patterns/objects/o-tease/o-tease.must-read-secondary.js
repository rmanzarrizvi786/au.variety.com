const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.prototype' );
const o_tease = clonedeep( o_tease_prototype );

const o_taxonomy_item_prototype = require( '../o-taxonomy-item/o-taxonomy-item.prototype' );
const o_taxonomy_item = clonedeep( o_taxonomy_item_prototype );

o_tease.o_tease_classes = 'lrv-u-flex';
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-width-177@tablet', 'u-width-25p' );
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-width-44p@mobile-max', '' );
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-margin-r-125@tablet', 'lrv-u-margin-r-1@tablet u-padding-l-075 u-padding-l-00@tablet lrv-u-padding-tb-075' );
o_tease.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-1x1';
o_tease.c_title.c_title_classes = 'lrv-u-font-weight-normal u-font-weight-bold@tablet u-font-size-15 u-font-size-14@tablet u-max-height-36em a-truncate-ellipsis u-line-height-120';
o_tease.c_link = null;
o_tease.c_timestamp = null;

module.exports = {
	...o_tease,
	c_span: o_taxonomy_item.c_span,
};
