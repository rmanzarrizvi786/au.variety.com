const clonedeep = require('lodash.clonedeep');
const c_tagline = clonedeep(require('@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype'));
const article_title = clonedeep( require( './article-title.prototype' ) );

c_tagline.c_tagline_text = 'Robert Pattison on Becoming Batman and Why the Lighthouse is Just Weird Enough';
c_tagline.c_tagline_classes = 'lrv-u-margin-tb-00 u-color-iron-grey';

article_title.article_title_markup = 'Darker Side';
article_title.article_title_classes = 'u-font-size-inherit lrv-u-font-weight-light';
article_title.c_tagline = c_tagline;

article_title.article_title_outer = true;
article_title.article_title_outer_classes = 'a-font-primary-regular-3xl u-padding-tb-075@tablet u-max-width-900 lrv-u-margin-lr-auto lrv-u-padding-tb-1';

module.exports = article_title;
