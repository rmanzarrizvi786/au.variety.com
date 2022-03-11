const clonedeep = require( 'lodash.clonedeep' );

const latest_news_river = clonedeep( require( './latest-news-river.prototype.js' ) );

latest_news_river.latest_news_river_classes += ' a-span2@tablet a-span3@desktop-xl lrv-u-height-100p';

latest_news_river.o_more_from_heading.c_heading.c_heading_text    = 'Recent Articles';
latest_news_river.o_more_from_heading.c_heading.c_heading_classes = 'lrv-u-font-weight-normal lrv-u-font-family-primary u-font-size-22 u-font-size-28@tablet u-line-height-1 lrv-u-padding-t-050@tablet';
latest_news_river.o_more_from_heading.o_more_from_heading_classes = ' lrv-u-flex  lrv-u-padding-b-050 u-border-b-6 u-border-color-pale-sky-2 u-margin-b-00@tablet ' +
	' lrv-u-margin-b-050 u-padding-b-075@tablet u-padding-t-050@tablet u-padding-t-075';

latest_news_river.latest_news_river_classes = ' u-padding-lr-0 lrv-u-background-color-white';
latest_news_river.latest_news_river_is_paged = true;

latest_news_river.o_subscribe_cta = null;

module.exports = {
	...latest_news_river,
};
