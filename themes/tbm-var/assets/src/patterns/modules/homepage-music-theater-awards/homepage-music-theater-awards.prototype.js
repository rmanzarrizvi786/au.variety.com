const clonedeep = require( 'lodash.clonedeep' );

const music_vertical_list_prototype = require( '../homepage-vertical-list/homepage-vertical-list.prototype' );
const music_vertical_list = clonedeep( music_vertical_list_prototype );
const theater_vertical_list = clonedeep( music_vertical_list_prototype );

const awards_curated_prototype = require( '../vip-curated/vip-curated.awards' );
const awards_curated = clonedeep( awards_curated_prototype );

music_vertical_list.o_more_from_heading.c_heading.c_heading_text = 'Music';

theater_vertical_list.o_more_from_heading.c_heading.c_heading_text = 'Theater';

awards_curated.vip_curated_classes += ' a-span2@desktop-xl';

module.exports = {
	homepage_music_theater_awards_classes: 'lrv-a-grid lrv-a-wrapper a-cols3@tablet a-cols4@desktop-xl',
	music_vertical_list,
	theater_vertical_list,
	awards_curated,
};
