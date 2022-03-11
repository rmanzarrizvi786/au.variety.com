const clonedeep = require( 'lodash.clonedeep' );
const o_tease_news_list = clonedeep( require( '../../objects/o-tease-news-list/o-tease-news-list.prototype' ) );
const o_sub_heading = clonedeep( require( '../../objects/o-sub-heading/o-sub-heading.prototype' ) );

o_sub_heading.o_sub_heading_classes = 'lrv-u-border-b-1 u-border-color-brand-secondary-40 u-padding-b-125@tablet lrv-u-padding-b-2@mobile-max u-margin-b-075 u-margin-b-125@tablet';

const c_dek = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' ) );

c_dek.c_dek_classes = 'lrv-u-font-family-secondary lrv-u-margin-a-00 lrv-u-font-size-14@tablet u-font-size-16@desktop';
c_dek.c_dek_text = 'This is what the news should sound like. The biggest stories of our time, told by the best journalists in the world. Hosted by Michael Barbaro. Twenty minutes a day, five days a week, ready by 6 a.m.';

o_tease_news_list.o_tease_list_item_classes = '';
const o_tease_news = clonedeep( o_tease_news_list.o_tease_list_items[0] );
o_tease_news.o_tease_classes = 'u-padding-b-125@tablet lrv-u-padding-b-2@mobile-max lrv-u-border-b-1 u-border-color-brand-secondary-40 u-margin-b-075 u-margin-b-125@tablet lrv-u-flex lrv-u-flex-direction-column@mobile-max';
o_tease_news.o_tease_meta_classes = 'lrv-u-flex lrv-u-align-items-center a-separator-r-1 a-separator-spacing--r-050 a-separator-spacing--r-075@tablet lrv-u-padding-b-050 lrv-u-padding-b-050 lrv-u-padding-b-050';
o_tease_news.c_title.c_title_classes = 'lrv-u-font-family-secondary lrv-u-font-weight-bold u-font-size-21@mobile-max lrv-u-font-size-18 u-font-size-21@desktop u-line-height-120 u-letter-spacing-0002 lrv-u-margin-b-075 u-letter-spacing-0002 lrv-u-margin-b-075';
o_tease_news.c_dek = c_dek;
o_tease_news.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-1x1';
o_tease_news.c_lazy_image.c_lazy_image_img_classes += ' u-border-color-link-water lrv-u-border-a-1';
o_tease_news.o_tease_primary_classes = '';
o_tease_news.o_tease_secondary_classes = 'lrv-u-flex-shrink-0 lrv-u-width-100p u-width-150@tablet u-width-200@desktop u-order-n1 lrv-a-glue-parent u-margin-r-125@tablet lrv-u-margin-b-1@mobile-max';
o_tease_news.c_timestamp = false;

const o_tease_news_last = clonedeep( o_tease_news );
o_tease_news_last.o_tease_classes = 'u-padding-b-125@tablet lrv-u-padding-b-2@mobile-max lrv-u-border-b-1 u-border-color-brand-secondary-40 lrv-u-margin-b-00 u-margin-b-125@tablet lrv-u-flex lrv-u-flex-direction-column@mobile-max';

o_tease_news_list.o_tease_list_items = [
	o_tease_news,
	o_tease_news,
	o_tease_news,
	o_tease_news,
	o_tease_news_last
];

module.exports = {
	stories_river_classes: '',
	o_sub_heading,
	o_tease_news_list,
};
