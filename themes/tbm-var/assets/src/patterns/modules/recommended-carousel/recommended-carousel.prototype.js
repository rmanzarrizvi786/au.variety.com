const clonedeep = require( 'lodash.clonedeep' );

const explore_playlists_prototype = require( '../explore-playlists/explore-playlists.prototype' );
const explore_playlists = clonedeep( explore_playlists_prototype );

const o_card_prototype = require( '../../objects/o-card/o-card.recommended' );
const o_card = clonedeep( o_card_prototype );

const o_more_link = clonedeep( require( '../../objects/o-more-link/o-more-link.blue.homepage' ) );

const {
	c_heading
} = explore_playlists;

explore_playlists.explore_playlists_classes = 'lrv-u-margin-b-1@tablet';

explore_playlists.special_reports_carousel_classes = explore_playlists.special_reports_carousel_classes.replace( 'u-background-color-picked-bluewood', '' );
explore_playlists.special_reports_carousel_classes = explore_playlists.special_reports_carousel_classes.replace( 'u-border-t-6', '' );
explore_playlists.special_reports_carousel_classes = explore_playlists.special_reports_carousel_classes.replace( 'u-padding-b-150', '' );
explore_playlists.special_reports_carousel_classes = explore_playlists.special_reports_carousel_classes.replace( 'u-padding-b-225@tablet', '' );
explore_playlists.special_reports_carousel_classes = explore_playlists.special_reports_carousel_classes.replace( 'lrv-u-padding-lr-1', '' );
explore_playlists.special_reports_carousel_classes = explore_playlists.special_reports_carousel_classes.replace( 'lrv-u-padding-t-050', '' );

explore_playlists.special_report_item_classes = explore_playlists.special_report_item_classes.replace( 'u-border-color-pale-sky-2', 'u-border-color-brand-secondary-40' );
explore_playlists.special_report_item_classes = explore_playlists.special_report_item_classes.replace( 'u-padding-lr-075', 'u-padding-r-075' );
explore_playlists.special_report_item_classes += ' u-margin-r-075 u-padding-r-1@tablet u-margin-r-1@tablet u-min-height-100p';

o_card.o_card_classes = o_card.o_card_classes.replace( 'u-width-190@tablet', 'u-width-155@tablet' );
o_card.o_card_classes = o_card.o_card_classes.replace( 'u-width-250@desktop-xl', 'u-width-205@desktop-xl' );

const o_card_vip = clonedeep( o_card );

o_card_vip.c_span.c_span_link_classes = o_card_vip.c_span.c_span_link_classes.replace( 'u-color-brand-secondary-60', 'u-color-brand-vip-primary' );
o_card_vip.c_span.c_span_text = o_card_vip.c_span.c_span_text = 'Vip';

explore_playlists.explore_playlists_items = [
	o_card_vip,
	o_card,
	o_card_vip,
	o_card,
	o_card,
	o_card_vip,
	o_card,
	o_card,
];

c_heading.c_heading_text = 'Recommended for you';
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'lrv-u-color-white', 'lrv-u-color-black' );
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'u-font-size-32@tablet', 'u-font-size-25@tablet' );
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'lrv-u-border-b-1', 'u-border-b-1@mobile-max' );
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'u-margin-b-125@tablet', 'u-margin-b-00' );
c_heading.c_heading_classes += ' u-letter-spacing-030 lrv-u-text-transform-uppercase u-border-color-brand-secondary-40 lrv-u-padding-tb-1';

explore_playlists.explore_playlists_classes += ' lrv-u-background-color-white u-padding-lr-075 u-padding-lr-1@tablet u-box-shadow-menu u-border-t-6 u-border-color-picked-bluewood lrv-u-padding-b-1 u-padding-b-2@tablet';
explore_playlists.o_more_link.c_link = false;

module.exports = {
	recommended_carousel_wrapper_classes: 'lrv-a-wrapper',
	explore_playlists,
};
