/**
 * @TODO: Worth creating a more generic carousel based on special reports
 */

const clonedeep = require( 'lodash.clonedeep' );

const explore_playlists_prototype = require( './explore-playlists.prototype.js' );
const explore_playlists = clonedeep( explore_playlists_prototype );

explore_playlists.explore_playlists_classes = 'u-background-color-picked-bluewood';
explore_playlists.explore_playlists_wrapper_classes = '';
explore_playlists.special_report_inner_classes += ' js-Flickity--wide';
explore_playlists.special_report_inner_classes = explore_playlists.special_report_inner_classes.replace( 'lrv-u-margin-r-1 lrv-u-margin-l-025', '' );
explore_playlists.special_reports_carousel_classes = explore_playlists.special_reports_carousel_classes.replace( 'lrv-u-padding-lr-1', '' );

module.exports = {
	...explore_playlists
};
