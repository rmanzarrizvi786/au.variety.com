const clonedeep = require( 'lodash.clonedeep' );

const article_title = clonedeep( require( '../../modules/article-title/article-title.prototype' ) );
article_title.article_title_classes = 'lrv-u-font-family-primary lrv-u-font-size-32 u-font-size-54@desktop-xl lrv-u-font-size-46@tablet u-padding-tb-075@tablet u-line-height-1@tablet lrv-u-margin-tb-1 lrv-u-text-align-center';

module.exports = article_title;
