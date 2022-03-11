const clonedeep = require( 'lodash.clonedeep' );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const c_link = clonedeep( c_link_prototype );

// Note: this should use c-tagline for the text, and should not be an object,
// rather, it should be all be in cta-subscribe module.

c_link.c_link_classes = 'lrv-a-unstyle-link lrv-u-margin-r-auto lrv-u-text-transform-uppercase lrv-u-font-weight-bold lrv-u-font-size-12 u-color-brand-secondary-50 lrv-u-font-family-secondary u-letter-spacing-012 lrv-a-icon-after a-icon-long-right-arrow-blue u-color-picked-bluewood:hover lrv-u-padding-tb-050';
c_link.c_link_text = 'Lorem Ipsum';

module.exports = {
	o_cta_classes: 'lrv-u-margin-tb-1',
	o_cta_text: 'Lorem Ipsum',
	o_cta_text_classes: 'lrv-u-margin-tb-00 u-font-size-15 u-font-size-18@tablet',
	c_link
};
