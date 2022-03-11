
const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const heading = clonedeep( c_heading_prototype );

const special_report_carousel_prototype = require( '../special-reports-carousel/special-reports-carousel.prototype.js' );
const special_report_carousel = clonedeep( special_report_carousel_prototype );

heading.c_heading_classes = 'lrv-u-border-b-1 lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-padding-b-075 u-border-color-link-water lrv-u-font-size-22 u-font-size-24@desktop-xl lrv-u-line-height-small@desktop-xl';
heading.c_heading_text = 'Amazon Prime';

const o_card_prototype = require( '../../objects/o-card/o-card.story' );

const o_card = clonedeep( o_card_prototype );
const o_card_first = clonedeep( o_card );
o_card.o_card_classes += ' lrv-u-margin-lr-050';
o_card_first.o_card_classes += ' lrv-u-margin-r-050';

special_report_carousel.special_report_items = null;
special_report_carousel.o_more_from_heading = null;

special_report_carousel.special_reports_carousel_classes = 'u-padding-b-150 lrv-u-margin-lr-auto';

special_report_carousel.special_report_inner_classes = 'js-Flickity--nav-top-right js-Flickity--isContained is-draggable u-padding-t-3@tablet u-padding-t-250';

special_report_carousel.special_report_item_classes = 'u-border-color-link-water lrv-u-flex u-min-height-100p';

const stories_slider_items = [
	o_card_first,
	o_card,
	o_card,
	o_card,
	o_card,
];

module.exports = {
	...special_report_carousel,
	stories_slider_classes: '',
	stories_slider_id_attr: heading.c_heading_text.toLowerCase().replace(' ','-'),
	stories_slider_offset_attr: false,
	stories_slider_wrapper_classes: 'u-margin-b-075 ',
	heading,
	stories_slider_items,
};
