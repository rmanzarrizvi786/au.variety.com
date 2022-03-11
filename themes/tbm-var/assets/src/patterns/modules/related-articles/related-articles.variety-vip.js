const clonedeep = require( 'lodash.clonedeep' );

const related_articles_prototype = require( '../related-articles/related-articles.prototype' );
const related_articles_el = clonedeep( related_articles_prototype );

const related_article_prototype = require( '../related-article/related-article.variety-vip' );
const related_article = clonedeep( related_article_prototype );
const related_article_secondary = clonedeep( related_article_prototype );

const c_icon_prototype = require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype.js' );
const c_icon = clonedeep( c_icon_prototype );

c_icon.c_icon_name = 'plus-medium';
c_icon.c_icon_classes = 'lrv-u-color-brand-primary u-height-24 u-width-24 u-margin-r-031@mobile-max u-margin-b-050@tablet u-margin-t-025@tablet';

related_articles_el.c_heading.c_heading_text = 'Related';
related_articles_el.c_heading.c_heading_classes = 'a-font-accent-m u-color-picked-bluewood u-letter-spacing-030 a-content-ignore';

related_articles_el.related_articles_classes = 'related-articles__vip lrv-u-background-color-white u-max-width-175@tablet';
related_articles_el.related_articles_heading_classes = 'lrv-u-flex u-align-items-center@mobile-max u-padding-lr-050@mobile-max u-background-color-brand-accent@mobile-max u-border-t-6@mobile-max u-border-color-brand-secondary-50 u-flex-direction-column@tablet';

related_article.c_heading.c_heading_link_classes += ' lrv-u-font-size-14';

// Secondary story doesn't display an image on VIP related.
related_article_secondary.c_lazy_image = false;
related_article_secondary.c_heading.c_heading_text = 'Fox News Seeks to Move Andrea Tantaros';
related_article_secondary.c_heading.c_heading_link_classes += ' lrv-u-font-size-14';

related_articles_el.related_articles = [
	related_article,
	related_article_secondary
];

related_articles_el.related_articles_wrap_classes = '';
related_articles_el.related_articles_list_classes = '',
related_articles_el.related_articles_outer_classes = 'a-floated-right@tablet',

module.exports = {
  ...related_articles_el,
  c_icon
};
