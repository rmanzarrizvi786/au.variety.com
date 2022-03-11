const clonedeep = require( 'lodash.clonedeep' );
const cxense_widget_prototype = require( '../cxense-widget/cxense-widget.prototype' );
const cxense_article_end_subscribe_widget = clonedeep( cxense_widget_prototype );
cxense_article_end_subscribe_widget.cxense_id_attr = 'cx-module-article-end';

module.exports = {
	cta_subscribe_classes: 'u-font-family-body',
	cxense_article_end_subscribe_widget
};
