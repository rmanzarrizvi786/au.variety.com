const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.square' );
const o_tease = clonedeep( o_tease_prototype );

const c_critics_pick_label_prototype = require( '../../components/c-critics-pick-label/c-critics-pick-label.secondary' );
const c_critics_pick_label = clonedeep( c_critics_pick_label_prototype );

const c_dek_prototype = require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' );
const c_dek = clonedeep( c_dek_prototype );

c_dek.c_dek_classes = 'u-font-size-13 lrv-u-color-black lrv-u-font-family-secondary u-margin-t-025 u-margin-t-050@tablet u-line-height-120 u-line-height-normal@tablet lrv-u-margin-b-00';
c_dek.c_dek_text = 'Scorsese’s mob epic, with Robert De Niro as a hitman and Al Pacino as an ego-drenched Jimmy Hoffa, is a coldly enthralling triumph.';

o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'u-padding-t-1@desktop-xl', 'u-padding-t-00@tablet' );
o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'u-padding-b-150@tablet', 'u-padding-b-00@tablet' );
o_tease.o_tease_classes += ' lrv-a-glue-parent u-border-b-1@mobile-max u-border-color-pale-sky-2 u-flex-direction-column@tablet';
o_tease.c_dek = c_dek;
o_tease.c_title.c_title_text = '‘Terminator: Dark Fate’';
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-size-14@tablet', 'u-font-size-18@tablet' );
o_tease.c_title.c_title_classes += ' u-margin-t-075@tablet lrv-u-font-family-secondary';
o_tease.o_tease_primary_classes += ' a-glue-parent@tablet';
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-order-n1@tablet', 'u-order-n1' );
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-width-44p@mobile-max', 'u-width-125' );
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-width-50@tablet', 'u-width-100p@tablet' );
o_tease.o_tease_secondary_classes += ' u-margin-r-075';
o_tease.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-1x1 a-crop-16x9@tablet';
o_tease.c_span = c_critics_pick_label.c_span;

c_dek.c_dek_classes = 'u-font-size-13 lrv-u-color-black lrv-u-font-family-secondary u-margin-t-025 u-margin-t-050@tablet u-line-height-120 u-line-height-normal@tablet lrv-u-margin-b-00';
c_dek.c_dek_text = 'Scorsese’s mob epic, with Robert De Niro as a hitman and Al Pacino as an ego-drenched Jimmy Hoffa, is a coldly enthralling triumph.';

module.exports = {
	...o_tease,
};
