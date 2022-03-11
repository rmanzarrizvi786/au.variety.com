const clonedeep = require( 'lodash.clonedeep' );

const video_menu_prototype = require( './video-menu.prototype.js' );
const video_menu = clonedeep( video_menu_prototype );

const {
	o_nav,
	o_drop_menu,
} = video_menu;

const c_link = o_nav.o_nav_list_items.pop();

c_link.c_link_text = 'Emmy Awards';

o_nav.o_nav_list_items = [ c_link ];

o_drop_menu.c_span.c_span_text = 'More Playlists';

module.exports = {
	...video_menu,
};
