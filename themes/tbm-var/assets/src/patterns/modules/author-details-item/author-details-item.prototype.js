const clonedeep = require( 'lodash.clonedeep' );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const c_link = clonedeep( c_link_prototype );

const c_timestamp_prototype = require( '@penskemediacorp/larva-patterns/components/c-timestamp/c-timestamp.prototype.js' );
const c_timestamp = clonedeep( c_timestamp_prototype );

c_link.c_link_classes = 'lrv-u-display-block lrv-u-font-family-secondary lrv-u-padding-t-050 lrv-u-font-weight-bold lrv-u-color-black u-color-black:hover u-font-size-13 u-font-size-15@tablet lrv-u-margin-b-050@mobile-max u-text-decoration-underline:hover';

c_timestamp.c_timestamp_classes = 'lrv-u-display-block u-font-family-basic u-font-size-13 a-hidden@desktop-max u-color-iron-grey';
c_timestamp.c_timestamp_text = '2 hours ago';

module.exports = {
	'author_details_item_classes': 'lrv-u-border-b-1 u-border-color-brand-secondary-40 lrv-u-padding-b-050',
	c_link,
	c_timestamp,
};
