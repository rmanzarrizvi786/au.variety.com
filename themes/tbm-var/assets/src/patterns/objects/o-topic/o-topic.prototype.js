const clonedeep = require( 'lodash.clonedeep' );

const c_icon_prototype = require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype.js' );
const c_icon = clonedeep( c_icon_prototype );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

const c_tagline_prototype = require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype.js' );
const c_tagline = clonedeep( c_tagline_prototype );

c_icon.c_icon_name = 'plus-medium';
c_icon.c_icon_classes = 'a-hidden@mobile-max lrv-u-color-brand-primary u-width-40 u-height-40';

c_heading.c_heading_classes = 'lrv-u-color-black lrv-u-color-brand-primary:hover lrv-u-font-family-secondary lrv-u-font-size-18 lrv-u-text-transform-uppercase u-letter-spacing-030 u-margin-t-075@tablet u-margin-b-050@tablet';
c_heading.c_heading_text = 'Disney';

c_tagline.c_tagline_classes = 'lrv-u-font-size-14 lrv-u-margin-b-0 lrv-u-text-align-center u-line-height-140 u-margin-t-025 u-color-dim-grey';
c_tagline.c_tagline_text = 'Hong Kong Disneyland bounced back in the third quarter.';

module.exports = {
	o_topic_classes: 'lrv-u-flex lrv-u-flex-direction-column lrv-u-align-items-center lrv-u-text-align-center lrv-u-color-black lrv-u-justify-content-center u-padding-t-075 u-background-color-porcelain u-padding-lr-250 u-padding-tb-150@tablet lrv-u-width-100p u-max-width-300@tablet lrv-u-padding-b-025 u-padding-b-050@tablet u-margin-b-2@tablet u-padding-lr-150@tablet',
	o_topic_url: '#',
	c_icon,
	c_heading,
	c_tagline
};
