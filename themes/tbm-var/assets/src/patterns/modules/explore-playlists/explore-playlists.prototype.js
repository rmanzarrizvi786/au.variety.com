/**
 * @TODO: Worth creating a more generic carousel based on special reports
 */

const clonedeep = require( 'lodash.clonedeep' );

const special_report_carousel_prototype = require( '../special-reports-carousel/special-reports-carousel.prototype.js' );
const special_report_carousel = clonedeep( special_report_carousel_prototype );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

const o_card_prototype = require( '../../objects/o-card/o-card.prototype' );
const o_card = clonedeep( o_card_prototype );

const { o_more_link } = special_report_carousel;

special_report_carousel.special_report_items = null;
special_report_carousel.o_more_from_heading = null;

special_report_carousel.special_reports_carousel_classes = special_report_carousel.special_reports_carousel_classes.replace( 'u-background-image-slash', 'u-background-color-picked-bluewood' );
special_report_carousel.special_reports_carousel_classes = special_report_carousel.special_reports_carousel_classes.replace( 'u-border-color-brand-secondary-50', 'u-border-color-pale-sky-2' );
special_report_carousel.special_reports_carousel_classes = special_report_carousel.special_reports_carousel_classes.replace( 'u-padding-b-250', 'u-padding-b-150' );
special_report_carousel.special_reports_carousel_classes = special_report_carousel.special_reports_carousel_classes.replace( 'u-padding-t-150@tablet', '' );
special_report_carousel.special_reports_carousel_classes = special_report_carousel.special_reports_carousel_classes.replace( 'u-padding-b-250@tablet', 'u-padding-b-225@tablet' );
special_report_carousel.special_reports_carousel_classes = special_report_carousel.special_reports_carousel_classes.replace( 'u-border-b-6', '' );
special_report_carousel.special_reports_carousel_classes += ' lrv-u-padding-t-050 lrv-u-margin-lr-auto lrv-u-padding-lr-1';
special_report_carousel.special_report_inner_classes = special_report_carousel.special_report_inner_classes.replace( 'js-Flickity--isWrapAround', 'js-Flickity--isContained' );
special_report_carousel.special_report_inner_classes += '';
special_report_carousel.special_report_item_classes = special_report_carousel.special_report_item_classes.replace( 'lrv-u-margin-lr-050', 'u-padding-lr-075' );
special_report_carousel.special_report_item_classes = special_report_carousel.special_report_item_classes.replace( 'u-margin-r-250@tablet', '' );
special_report_carousel.special_report_item_classes += ' u-border-r-1 u-border-color-pale-sky-2';

c_heading.c_heading_classes = 'lrv-u-border-b-1 lrv-u-color-white lrv-u-font-family-secondary lrv-u-font-size-16 lrv-u-font-weight-bold lrv-u-padding-b-050 lrv-u-text-align-center@mobile-max u-text-transform-uppercase@mobile-max u-border-color-pale-sky-2 u-letter-spacing-002@mobile-max lrv-u-margin-b-1 u-font-size-32@tablet u-padding-b-1@tablet u-margin-b-125@tablet';
c_heading.c_heading_text = 'Explore Playlists';

o_more_link.o_more_link_classes += ' lrv-u-display-none';

const explore_playlists_items = [
	o_card,
	o_card,
	o_card,
	o_card,
	o_card,
];

module.exports = {
	...special_report_carousel,
	explore_playlists_classes: 'lrv-u-margin-b-2',
	explore_playlists_wrapper_classes: '',
	c_heading,
	explore_playlists_items,
};
