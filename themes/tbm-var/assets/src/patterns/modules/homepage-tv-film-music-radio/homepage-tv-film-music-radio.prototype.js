const clonedeep = require( 'lodash.clonedeep' );

const horizontal_row_prototype = require( '../homepage-horizontal-row/homepage-horizontal-row.prototype' );
const horizontal_row_data = clonedeep( horizontal_row_prototype );

module.exports = {
	homepage_tv_film_music_theater_classes: '',
	horizontal_row_data
};
