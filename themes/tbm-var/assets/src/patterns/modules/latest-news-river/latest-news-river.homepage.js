const clonedeep = require( 'lodash.clonedeep' );

const latest_news_river_prototype = require( './latest-news-river.prototype.js' );
const latest_news_river = clonedeep( latest_news_river_prototype );

latest_news_river.latest_news_river_classes += ' a-span2@tablet a-span3@desktop-xl lrv-u-height-100p';

module.exports = {
	...latest_news_river,
};
