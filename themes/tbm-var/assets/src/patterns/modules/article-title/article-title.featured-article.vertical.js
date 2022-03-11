const clonedeep = require('lodash.clonedeep');

const article_title = clonedeep( require( './article-title.featured-article' ) );

article_title.article_title_outer_classes += ' lrv-u-padding-lr-1 u-padding-lr-3@tablet';
article_title.c_tagline.c_tagline_classes = 'lrv-u-margin-tb-00 u-color-iron-grey u-color-brand-secondary-30@tablet';

module.exports = article_title;
