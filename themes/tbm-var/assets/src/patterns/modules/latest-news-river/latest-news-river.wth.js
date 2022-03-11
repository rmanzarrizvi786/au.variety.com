const clonedeep = require( 'lodash.clonedeep' );
const { c_heading } = require('../docs-classics/docs-classics.prototype.js');

const latest_news_river_prototype = require( './latest-news-river.prototype.js' );
const latest_news_river = clonedeep( latest_news_river_prototype );

const c_dek_prototype = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' ) );
const c_dek = clonedeep( c_dek_prototype );

c_dek.c_dek_classes = 'lrv-u-font-family-secondary lrv-u-margin-b-00 lrv-u-margin-t-050 u-line-height-140 lrv-u-font-size-14@tablet u-font-size-16@desktop';
c_dek.c_dek_text = 'Netflix\'s "Crime Scene: The Vanishing at the Cecil Hotel" takes on the Elisa Lam case that\'s stumped detectives and "web sleuths" for years.';

latest_news_river.o_tease_news_list_primary.o_tease_list_items.map( item => {
	item.o_tease_classes += ' lrv-u-flex-direction-column@mobile-max';
	item.o_tease_meta_classes = 'lrv-u-flex lrv-u-align-items-center a-separator-r-1 a-separator-spacing--r-050 a-separator-spacing--r-075@tablet lrv-u-padding-b-050 lrv-u-padding-b-050';
	item.c_dek = c_dek;
	item.c_timestamp = false;
	item.o_tease_secondary_classes = 'u-order-n1 lrv-u-margin-r-1@tablet lrv-a-glue-parent lrv-u-margin-b-1@mobile-max';
	item.c_title.c_title_classes = 'lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-18@mobile-max lrv-u-font-size-16 u-line-height-140 lrv-u-font-size-18@desktop u-line-height-120@desktop u-margin-b-050@tablet';
	item.c_lazy_image.c_lazy_image_classes = 'lrv-u-width-100p u-width-240@tablet u-width-300@desktop';
});


latest_news_river.o_tease_news_list_secondary.o_tease_list_items.map( item => {
	item.o_tease_classes += ' lrv-u-flex-direction-column@mobile-max';
	item.o_tease_meta_classes = 'lrv-u-flex lrv-u-align-items-center a-separator-r-1 a-separator-spacing--r-050 a-separator-spacing--r-075@tablet lrv-u-padding-b-050 lrv-u-padding-b-050';
	item.c_dek = c_dek;
	item.c_timestamp = false;
	item.o_tease_secondary_classes = 'u-order-n1 lrv-u-margin-r-1@tablet lrv-a-glue-parent lrv-u-margin-b-1@mobile-max';
	item.c_title.c_title_classes = 'lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-18 u-font-size-21@desktop-xl u-line-height-120';
	item.c_lazy_image.c_lazy_image_classes = 'lrv-u-width-100p u-width-240@tablet u-width-300@desktop';
});

latest_news_river.o_tease_news_list_primary.o_tease_list_item_classes = 'lrv-u-padding-b-1 lrv-u-border-b-1 u-border-color-brand-secondary-40 lrv-u-margin-b-1 u-margin-b-075@mobile-max lrv-u-padding-b-2@mobile-max';
latest_news_river.o_tease_news_list_secondary.o_tease_list_item_classes = 'lrv-u-padding-b-1 lrv-u-border-b-1 u-border-color-brand-secondary-40 lrv-u-margin-b-1 u-margin-b-075@mobile-max lrv-u-padding-b-2@mobile-max';
latest_news_river.latest_news_river_classes = 'lrv-u-border-t-3 u-border-color-picked-bluewood lrv-u-background-color-white';

latest_news_river.o_more_from_heading.o_more_from_heading_classes = 'lrv-u-flex lrv-u-align-items-center  u-border-b-1 u-border-color-brand-secondary-40 u-margin-b-00@tablet';
latest_news_river.o_more_from_heading.c_heading.c_heading_classes = 'a-font-accent-m lrv-u-text-align-center lrv-u-padding-t-050 lrv-u-padding-b-1 u-letter-spacing-015-important u-letter-spacing-025@desktop-xl lrv-u-line-height-small';

module.exports = {
	...latest_news_river,
};
