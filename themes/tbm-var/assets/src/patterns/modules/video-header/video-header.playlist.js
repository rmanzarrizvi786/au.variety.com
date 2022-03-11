const clonedeep = require( 'lodash.clonedeep' );

const video_header_prototype = require( './video-header.prototype.js' );
const video_header = clonedeep( video_header_prototype );

const video_menu = require( '../video-menu/video-menu.playlist.js' );

const video_showcase = require( '../video-showcase/video-showcase.playlist' );

const {
	video_menu_mobile
} = video_header;

video_header.video_menu = clonedeep( video_menu );

video_header.video_menu.video_menu_classes = video_header.video_menu.video_menu_classes.replace( 'u-max-width-900', 'u-max-width-968' );
video_header.video_menu.video_menu_classes += ' u-margin-t-n2 lrv-u-border-b-1 u-border-color-pale-sky-2';

video_menu_mobile.c_span.c_span_text = 'Emmy Awards';

module.exports = {
	...video_header,
	video_showcase,
};