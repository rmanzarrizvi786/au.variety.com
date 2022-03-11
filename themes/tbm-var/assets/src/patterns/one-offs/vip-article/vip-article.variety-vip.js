const clonedeep = require( 'lodash.clonedeep' );

const article_header_prototype = require( '../../modules/article-header/article-header.variety-vip.js' );
const article_header = clonedeep( article_header_prototype );

const a_content_prototype = require( '../a-content/a-content.variety-vip.js' );
const a_content = clonedeep( a_content_prototype );

const more_from_widget_prototype = require( '../../modules/more-from-widget-article/more-from-widget-article.prototype.js' );
const more_from_widget = clonedeep( more_from_widget_prototype );

const header = require( '../../modules/header-vip/header-vip.prototype' );

const cta_banner = require( '../../modules/cta-banner/cta-banner.prototype' );

const cta_subscribe = require( '../../modules/cta-subscribe/cta-subscribe.variety-vip' );

const footer = require( '../../modules/footer/footer.prototype' );

module.exports = {
	header,
	cta_banner,
	article_header,
	more_from_widget,
	footer,
	cta_subscribe
};
