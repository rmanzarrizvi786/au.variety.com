const clonedeep = require( 'lodash.clonedeep' );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const c_link = clonedeep( c_link_prototype );

c_link.c_link_classes = 'u-font-size-13 u-font-size-16@tablet u-color-pale-sky-2 u-color-black:hover o-comments-link--icon lrv-a-icon-before a-icon-comments-black lrv-u-text-transform-uppercase lrv-u-border-a-1 u-border-color-black lrv-u-display-flex lrv-u-align-items-center lrv-u-justify-content-center lrv-u-line-height-large lrv-u-padding-a-050 lrv-u-width-100p u-font-family-basic';
c_link.c_link_text = '15 Comments';
c_link.c_link_url = '#article-comments';

module.exports = {
	o_comments_link_classes: 'lrv-u-margin-b-2',
	c_link
};
