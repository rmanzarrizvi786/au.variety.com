const clonedeep = require( 'lodash.clonedeep' );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const c_link = clonedeep( c_link_prototype );

c_link.c_link_classes = 'lrv-a-unstyle-link lrv-u-margin-r-auto lrv-u-text-transform-uppercase lrv-u-font-weight-bold u-font-size-11 u-color-brand-secondary-50 lrv-u-font-family-secondary u-letter-spacing-012 lrv-a-icon-after a-icon-long-right-arrow';
c_link.c_link_text = 'More';

module.exports = {
	o_more_link_classes: '',
	c_link
};
