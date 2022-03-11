const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.homepage' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const o_tease_news_list_prototype = require( '../../objects/o-tease-news-list/o-tease-news-list.prototype' );

o_tease_news_list_prototype.o_tease_list_items.map( ( item ) => {
	item.o_tease_meta_classes += ' lrv-u-padding-b-050';
} );

const o_tease_news_list_primary = clonedeep( o_tease_news_list_prototype );
const o_tease_news_list_secondary = clonedeep( o_tease_news_list_primary );

const cxense_widget_prototype = require( '../cxense-widget/cxense-widget.prototype' );
const cxense_subscribe_widget = clonedeep( cxense_widget_prototype );
cxense_subscribe_widget.cxense_id_attr = 'cx-module-mid-river';

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.blue' );
const o_more_link = clonedeep( o_more_link_prototype );

const o_more_link_previous = clonedeep( require( '../../objects/o-more-link/o-more-link.blue.previous' ) );

o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'lrv-u-padding-t-050@tablet', 'u-padding-t-050@tablet' );
o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'u-margin-b-125@tablet', 'u-padding-b-075@tablet' );
o_more_from_heading.o_more_from_heading_classes += ' u-border-b-1 u-border-color-brand-secondary-40 u-margin-b-00@tablet';
o_more_from_heading.c_heading.c_heading_text = 'Latest News';

o_tease_news_list_primary.o_tease_list_classes += ' u-margin-t-075 u-margin-t-125@tablet';

o_more_link.o_more_link_classes = 'lrv-u-margin-l-auto';
o_more_link.c_link.c_link_text = 'More News';

module.exports = {
	latest_news_river_classes: 'u-border-t-6 u-border-color-picked-bluewood u-box-shadow-menu u-padding-lr-075 u-padding-lr-1@tablet lrv-u-background-color-white',
	latest_news_river_is_paged: false,
	o_more_link_previous,
	o_more_from_heading,
	o_tease_news_list_primary,
	o_tease_news_list_secondary,
	cxense_subscribe_widget,
	o_more_link,
};
