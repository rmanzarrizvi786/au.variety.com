const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

const video_menu_prototype = require( '../video-menu/video-menu.prototype.js' );
const video_menu = clonedeep( video_menu_prototype );

const video_menu_mobile_prototype = require( '../video-menu-mobile/video-menu-mobile.prototype.js' );
const video_menu_mobile = clonedeep( video_menu_mobile_prototype );

const video_showcase_prototype = require( '../video-showcase/video-showcase.prototype' );
const video_showcase = clonedeep( video_showcase_prototype );

c_heading.c_heading_classes = 'lrv-u-font-family-primary lrv-u-font-size-28 lrv-u-text-align-center u-colors-map-accent-c-100 u-letter-spacing-001 u-line-height-110 u-font-size-65@tablet u-font-size-70@desktop-xl lrv-u-font-weight-normal';
c_heading.c_heading_text = 'Video';

video_menu.video_menu_classes += ' lrv-u-border-b-1 u-border-color-pale-sky-2'

video_menu_mobile.c_span.c_span_classes += ' lrv-u-padding-b-050 lrv-u-border-b-1 u-border-color-loblolly-grey';

module.exports = {
	video_header_classes: 'lrv-u-margin-lr-auto u-background-color-picked-bluewood',
	video_header_wrapper_classes: '',
	video_header_header_classes: 'u-margin-b-075 lrv-u-padding-t-050',
	video_header_data_attrs: 'data-video-showcase',
	video_header_videos_classes: '',
	c_heading,
	video_menu,
	video_menu_mobile,
	video_showcase,
};
