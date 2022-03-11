const clonedeep = require( 'lodash.clonedeep' );

const c_heading = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' ) );
const c_tagline = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' ) );

c_heading.c_heading_text = 'Browse By Streamer';
c_heading.c_heading_classes = 'lrv-u-font-size-24 lrv-u-font-size-28@tablet lrv-u-font-size-32@desktop-xl lrv-u-font-family-secondary lrv-u-line-height-small';

c_tagline.c_tagline_classes = 'lrv-u-font-size-16 lrv-u-font-size-18@desktop-xl lrv-u-font-family-secondary lrv-u-margin-t-050 lrv-u-margin-b-2'

module.exports = {
	streamers_section_header_classes: 'lrv-a-wrapper lrv-u-margin-b-2',
	streamers_section_header_wrapper_classes: 'lrv-u-border-t-3 lrv-u-border-color-brand-primary lrv-u-padding-t-075',
	c_heading: c_heading,
	c_tagline: c_tagline
}
