const clonedeep = require( 'lodash.clonedeep' );

const c_timestamp_prototype = require( '@penskemediacorp/larva-patterns/components/c-timestamp/c-timestamp.prototype.js' );
const c_timestamp = clonedeep( c_timestamp_prototype );

const breadcrumbs_prototype = require( '../breadcrumbs/breadcrumbs.variety-vip.js' );

c_timestamp.c_timestamp_classes = 'lrv-u-text-transform-uppercase u-color-iron-grey a-hidden@mobile-max u-font-family-accent u-letter-spacing-003 u-font-size-13';
c_timestamp.c_timestamp_text = 'August 29, 2016 3:10PM';

module.exports = {
	article_meta_classes: 'lrv-u-align-items-center lrv-u-flex-direction-column@mobile-max u-align-items@mobile-max u-border-color-dusty-grey lrv-u-flex lrv-u-justify-content-space-between u-border-b-1@tablet u-margin-b-150@mobile-max lrv-u-padding-t-050@tablet u-padding-b-075@tablet',
	breadcrumbs: breadcrumbs_prototype,
	c_timestamp
};
