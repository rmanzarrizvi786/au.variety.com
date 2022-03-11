const clonedeep = require( 'lodash.clonedeep' );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const c_link = clonedeep( c_link_prototype );

c_link.c_link_classes = 'lrv-a-unstyle-link lrv-u-margin-l-auto lrv-u-text-transform-uppercase lrv-u-font-weight-bold lrv-u-font-size-12 u-color-brand-secondary-50 lrv-u-font-family-secondary u-letter-spacing-012 lrv-a-icon-after a-icon-long-right-arrow u-padding-lr-2@mobile-max';
c_link.c_link_text = 'More Video';
c_link.c_link_url = '#more-videos';

module.exports = {
	c_link,
};
