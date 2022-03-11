const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.vertical-list.primary' );
const o_tease = clonedeep( o_tease_prototype );

const c_critics_pick_label_prototype = require( '../../components/c-critics-pick-label/c-critics-pick-label.protoype' );
const c_critics_pick_label = clonedeep( c_critics_pick_label_prototype );

const c_dek_prototype = require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' );
const c_dek = clonedeep( c_dek_prototype );

o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'lrv-u-border-b-1', 'u-border-b-1@mobile-max' );
o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'lrv-u-margin-b-1@tablet', '' );
o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'u-padding-b-125@tablet', 'u-padding-b-00@tablet' );
o_tease.c_lazy_image.c_lazy_image_crop_class = 'a-crop-327x217 a-crop-16x9@tablet';
o_tease.c_span = c_critics_pick_label.c_span;
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-size-15', 'lrv-u-font-size-18' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-size-16@tablet', '' );
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-min-height-55', '' );
o_tease.c_title.c_title_classes += ' u-margin-t-075@tablet';

c_dek.c_dek_classes = 'u-font-size-13 lrv-u-color-black lrv-u-font-family-secondary u-margin-t-025 u-margin-t-050@tablet u-line-height-120 u-line-height-normal@tablet lrv-u-margin-b-00';
c_dek.c_dek_text = 'Scorsese’s mob epic, with Robert De Niro as a hitman and Al Pacino as an ego-drenched Jimmy Hoffa, is a coldly enthralling triumph.';

o_tease.c_dek = c_dek;
o_tease.o_tease_primary_classes += ' lrv-a-glue-parent';

module.exports = {
	...o_tease
};
