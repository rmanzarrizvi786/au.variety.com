const clonedeep = require( 'lodash.clonedeep' );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const c_link = clonedeep( c_link_prototype );

c_link.c_link_classes = 'u-font-size-11 u-font-size-18@tablet u-color-pale-sky-2 u-color-black:hover o-comments-link--icon lrv-a-icon-before a-icon-comments a-children-icon-spacing-0 u-font-family-basic';
c_link.c_link_text = '15';
c_link.c_link_url = '#article-comments';

module.exports = {
	o_comments_link_classes: '',
	c_link
};
