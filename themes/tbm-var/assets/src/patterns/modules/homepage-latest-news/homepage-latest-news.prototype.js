const clonedeep = require( 'lodash.clonedeep' );

const latest_news_river_prototype = require( '../latest-news-river/latest-news-river.prototype' );
const latest_news_river = clonedeep( latest_news_river_prototype );

const homepage_vertical_list_prototype = require( '../homepage-vertical-list/homepage-vertical-list.special' );
const homepage_vertical_list = clonedeep( homepage_vertical_list_prototype );

latest_news_river.latest_news_river_classes += ' lrv-u-height-100p';

module.exports = {
	latest_news_river,
	homepage_vertical_list
};
