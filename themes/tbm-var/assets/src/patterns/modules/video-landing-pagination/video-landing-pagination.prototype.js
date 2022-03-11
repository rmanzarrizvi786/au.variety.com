const clonedeep = require( 'lodash.clonedeep' );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const c_link_next = clonedeep( c_link_prototype );
const c_link_previous = clonedeep( c_link_prototype );

// @TODO This link format is being repeated a lot, move to custom component
c_link_next.c_link_classes = 'lrv-a-unstyle-link lrv-u-margin-l-auto lrv-u-text-transform-uppercase lrv-u-font-weight-bold u-font-size-11 u-color-brand-secondary-50 lrv-u-font-family-secondary u-letter-spacing-012 lrv-a-icon-after a-icon-long-right-arrow u-padding-lr-2@mobile-max';
c_link_next.c_link_text = 'More Video';

c_link_previous.c_link_classes = 'lrv-a-unstyle-link lrv-u-margin-r-auto lrv-u-text-transform-uppercase lrv-u-font-weight-bold u-font-size-11 u-color-brand-secondary-50 lrv-u-font-family-secondary u-letter-spacing-012 lrv-a-icon-before a-icon-long-left-arrow u-padding-lr-2@mobile-max';
c_link_previous.c_link_text = 'Previous Video';

module.exports = {
	video_landing_pagination_classes: 'lrv-u-flex lrv-u-align-items-center lrv-u-margin-lr-auto u-margin-t-025 lrv-u-margin-b-050@mobile-max u-margin-b-225@tablet',
	c_link_next,
  c_link_previous,
};
