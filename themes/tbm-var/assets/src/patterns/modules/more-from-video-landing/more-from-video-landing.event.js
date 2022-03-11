const clonedeep = require( 'lodash.clonedeep' );

const more_from_video_landing_prototype = require( './more-from-video-landing.prototype' );
const more_from_video_landing = clonedeep( more_from_video_landing_prototype );

const { o_more_from_heading } = more_from_video_landing;

o_more_from_heading.o_more_from_heading_classes = 'lrv-a-wrapper lrv-u-flex u-justify-content-center@mobile-max lrv-u-align-items-center lrv-u-text-align-center lrv-u-padding-tb-050 lrv-u-margin-b-050 lrv-u-margin-b-1@tablet';

o_more_from_heading.c_v_icon = null;

o_more_from_heading.c_heading.c_heading_classes = 'u-text-transform-uppercase@mobile-max u-font-family-primary@mobile-max lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-32 u-letter-spacing-040@mobile-max';
o_more_from_heading.c_heading.c_heading_text = 'More From This Event';

module.exports = {
	...more_from_video_landing
};
