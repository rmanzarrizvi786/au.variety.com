const clonedeep = require( 'lodash.clonedeep' );

const video_landing_pagination_prototype = require( './video-landing-pagination.prototype' );
const video_landing_pagination = clonedeep( video_landing_pagination_prototype );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const c_link_previous = clonedeep( c_link_prototype );

c_link_previous.c_link_classes = 'lrv-a-unstyle-link lrv-u-margin-r-auto lrv-u-text-transform-uppercase lrv-u-font-weight-bold u-font-size-11 u-color-brand-secondary-50 lrv-u-font-family-secondary u-letter-spacing-012 lrv-a-icon-before a-icon-long-left-arrow u-padding-lr-2@mobile-max';
c_link_previous.c_link_text = 'Previous';

module.exports = {
	...video_landing_pagination,
	c_link_previous,
};
