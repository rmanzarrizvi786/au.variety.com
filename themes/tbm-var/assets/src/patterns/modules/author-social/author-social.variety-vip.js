const clonedeep = require( 'lodash.clonedeep' );

const author_prototype = require( '../author/author.variety-vip.js' );
const author = clonedeep( author_prototype );

const social_share_prototype = require( '../social-share/social-share.variety-vip.js' );
const social_share = clonedeep( social_share_prototype );

const c_timestamp_prototype = require( '@penskemediacorp/larva-patterns/components/c-timestamp/c-timestamp.prototype.js' );
const c_timestamp = clonedeep( c_timestamp_prototype );

social_share.social_share_classes = 'lrv-u-margin-t-1@mobile-max';
social_share.social_share_item_classes = 'lrv-u-flex lrv-u-align-items-center lrv-u-margin-b-050@mobile-max';

c_timestamp.c_timestamp_classes += ' u-font-size-13 u-font-family-accent u-color-iron-grey lrv-u-text-transform-uppercase lrv-u-padding-b-050 lrv-u-border-t-1 u-margin-t-075@mobile-max u-padding-t-075@mobile-max u-letter-spacing-003';
c_timestamp.c_timestamp_text = 'AUGUST 29, 2016 3:10PM';

author.author_content_classes += ' lrv-u-margin-t-050';

module.exports = {
		author_social_classes: 'lrv-a-glue-parent',
		author_social_share_desktop_classes: 'lrv-a-glue lrv-a-glue--r-0 lrv-a-glue--t-0 a-hidden@mobile-max',
		author_social_share_mobile_classes: 'a-hidden@tablet a-hidden@desktop u-justify-content-center\@mobile-max lrv-u-flex',
		author_social_timestamp_classes: 'lrv-u-flex a-hidden@tablet a-hidden@desktop u-justify-content-center\@mobile-max ',
		author,
		social_share,
		c_timestamp
};
