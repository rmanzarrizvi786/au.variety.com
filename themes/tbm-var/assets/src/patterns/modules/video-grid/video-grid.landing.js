const clonedeep = require( 'lodash.clonedeep' );

const video_grid_prototype = require( './video-grid.variety-vip' );
const video_grid = clonedeep( video_grid_prototype );

// Prevent adding too many classes
video_grid.video_items[0].o_indicator.c_span.c_span_classes += ' u-letter-spacing-009@mobile-max';

video_grid.video_items.forEach(	item => {
	item.o_video_card_permalink_url = '#single-url-in-loop';
	item.o_indicator.c_span.c_span_url = '#taxonomy-url';
	item.c_heading.c_heading_text = 'Media Disruptors 2020 Panel';
	item.c_heading.c_heading_url = '#single-url-in-loop';
} );

module.exports = {
	...video_grid,
};
