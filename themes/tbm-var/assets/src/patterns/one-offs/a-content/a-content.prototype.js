const clonedeep = require( 'lodash.clonedeep' );

const related_articles_prototype = require( '../../modules/related-articles/related-articles.prototype.js' );
const related_articles = clonedeep( related_articles_prototype );

const article_checks_prototype = require( '../../modules/article-checks/article-checks.prototype' );
const article_checks = clonedeep( article_checks_prototype );

const cta_subscribe_prototype = require( '../../modules/cta-subscribe/cta-subscribe.prototype' );
const cta_subscribe = clonedeep( cta_subscribe_prototype );

const comments_button_prototype = require( '../../modules/comments-button/comments-button.prototype' );;
const comments_button = clonedeep( comments_button_prototype );

module.exports = {
	a_content_classes: '',
	related_articles,
	article_checks,
	comments_button,
	cta_subscribe,
};
