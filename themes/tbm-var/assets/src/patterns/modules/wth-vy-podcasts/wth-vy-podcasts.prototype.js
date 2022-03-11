const clonedeep = require( 'lodash.clonedeep' );

const o_sub_heading = clonedeep( require( '../../objects/o-sub-heading/o-sub-heading.prototype' ) );
const c_dek = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' ) );
const c_link = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' ) );
const o_tease = clonedeep( require( '../../objects/o-tease/o-tease.prototype' ) );
const o_tease_list = clonedeep( require( '../../objects/o-tease-list/o-tease-list.prototype' ) );

o_sub_heading.o_sub_heading_classes = 'u-padding-b-125 u-margin-b-125 lrv-u-border-b-1 u-border-color-brand-secondary-40';
o_sub_heading.c_heading.c_heading_text = 'Variety Produced';

c_dek.c_dek_classes = 'lrv-u-font-family-secondary lrv-u-margin-a-00 lrv-u-font-size-14@tablet u-font-size-16@desktop';
c_dek.c_dek_text = 'Keep it in the family with our suite of Variety produced podcasts covering everything from awards, Broadway and business.';

o_tease.o_tease_classes = 'lrv-u-flex lrv-u-flex-direction-column@mobile-max u-padding-r-125@tablet';
o_tease.c_title.c_title_text = 'Awards Circuit';
o_tease.c_title.c_title_classes = 'lrv-u-font-family-secondary lrv-u-font-weight-bold u-font-size-21@mobile-max lrv-u-font-size-18 u-font-size-21@desktop u-line-height-120 u-letter-spacing-0002 lrv-u-margin-b-075 u-letter-spacing-0002 lrv-u-margin-b-075';
o_tease.c_dek = c_dek;
o_tease.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-1x1';
o_tease.c_lazy_image.c_lazy_image_img_classes += ' u-border-color-link-water lrv-u-border-a-1';
o_tease.o_tease_primary_classes = 'lrv-u-flex lrv-u-flex-direction-column';
o_tease.o_tease_secondary_classes = 'lrv-u-flex-shrink-0 lrv-u-width-100p u-width-150@tablet u-width-200@desktop u-order-n1 lrv-a-glue-parent u-margin-r-125@tablet lrv-u-margin-b-1@mobile-max';
o_tease.c_link = c_link;

o_tease_list.o_tease_list_classes += ' lrv-a-grid a-cols2@tablet u-grid-gap-0 u-align-items-stretch';
o_tease_list.o_tease_list_item_classes = 'lrv-u-border-b-1 u-border-color-brand-secondary-40 lrv-u-padding-b-1 lrv-u-margin-b-1 u-margin-b-075@mobile-max';
c_link.c_link_classes += ' a-font-secondary-bold-4xs lrv-u-text-transform-uppercase u-color-action-blue u-color-action-blue:hover u-letter-spacing-2 a-separator-before lrv-u-padding-t-075 lrv-u-margin-t-075 u-order-5 u-padding-b-1@mobile-max';
c_link.c_link_text = 'Listen';
o_tease.c_timestamp = false;

o_tease_list.o_tease_list_items = [
	o_tease,
	o_tease,
	o_tease,
	o_tease
];

module.exports = {
	wth_vy_podcasts_classes: '',
	o_sub_heading,
	o_tease_list,
};
