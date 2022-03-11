const clonedeep = require( 'lodash.clonedeep' );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const c_link = clonedeep( c_link_prototype );

const c_timestamp_prototype = require( '@penskemediacorp/larva-patterns/components/c-timestamp/c-timestamp.prototype.js' );
const c_timestamp = clonedeep( c_timestamp_prototype );

c_link.c_link_classes = 'lrv-u-font-family-secondary lrv-u-padding-tb-050 lrv-u-font-weight-bold lrv-u-color-black lrv-u-font-size-14@tablet-max lrv-u-font-size-18@tablet lrv-u-margin-b-050@mobile-max u-line-height-1@tablet';

c_timestamp.c_timestamp_classes = 'u-font-family-accent u-color-iron-grey lrv-u-font-size-14 a-hidden@desktop-max';
c_timestamp.c_timestamp_text = '2 hours ago ';

module.exports = {
	'author_details_item_classes': 'lrv-u-flex lrv-u-justify-content-space-between lrv-u-align-items-center lrv-u-border-b-1 u-border-color-loblolly-grey',
	c_link,
	c_timestamp,
};
