const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

const related_article_prototype = require( '../related-article/related-article.prototype.js' );
const related_article = clonedeep( related_article_prototype );
const related_article_secondary = clonedeep( related_article_prototype );

const related_articles = [
	related_article,
	related_article_secondary
];

c_heading.c_heading_text = 'Related Stories';
c_heading.c_heading_classes = 'lrv-u-font-family-secondary lrv-u-font-size-18 u-color-picked-bluewood a-content-ignore';

module.exports = {
	related_articles_classes: 'related-articles--one-off u-border-t-6 u-border-color-brand-primary lrv-u-padding-t-050 lrv-u-margin-tb-1 u-max-width-100vw',
	related_articles_heading_classes: 'lrv-u-padding-b-050 u-border-b-1 u-border-color-loblolly-grey',
	related_articles_wrap_classes: 'lrv-u-overflow-auto lrv-u-width-100p u-border-b-1 u-border-color-loblolly-grey',
	related_articles_list_classes: 'lrv-u-padding-t-050 lrv-u-padding-b-1 lrv-u-flex u-min-width-580@mobile-max u-min-width-656',
	related_articles_outer_classes: '',
	c_heading,
	related_articles
};
