
const clonedeep = require( 'lodash.clonedeep' );

const article_title = clonedeep( require( '../../modules/article-title/article-title.prototype' ) );
article_title.article_title_classes = 'lrv-u-font-family-primary u-font-weight-medium lrv-u-line-height-small lrv-u-text-align-center lrv-u-text-transform-uppercase u-margin-t-075@tablet lrv-u-padding-tb-050 lrv-u-padding-lr-2 u-font-size-50 u-font-size-70@tablet u-letter-spacing-2';

module.exports = article_title;
