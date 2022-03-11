const clonedeep = require( 'lodash.clonedeep' );

const o_card_prototype = require( './o-card.prototype' );
const o_card = clonedeep( o_card_prototype );

const o_span_group_prototype = require( '@penskemediacorp/larva-patterns/objects/o-span-group/o-span-group.prototype' );
const o_span_group = clonedeep( o_span_group_prototype );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' );
const c_span_critical_reviews = clonedeep( c_span_prototype );
const c_span_new_arrival = clonedeep( c_span_prototype );

c_span_critical_reviews.c_span_text = 'Critics Pick';
c_span_critical_reviews.c_span_classes = 'u-color-dark-grey lrv-u-text-transform-uppercase lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-font-family-secondary u-letter-spacing-012';
c_span_new_arrival.c_span_text = 'New Arrival';
c_span_new_arrival.c_span_classes = 'u-color-strong-blue lrv-u-text-transform-uppercase lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-font-family-secondary u-letter-spacing-012';

const c_link_logo_prototype = require( '../../components/c-link-logo/c-link-logo.prototype.js' );
const external_link_url = clonedeep( c_link_logo_prototype );
external_link_url.c_link_logo_classes += ' a-separator-before lrv-u-padding-t-075 lrv-u-margin-t-075';
external_link_url.c_link_logo_text_classes = 'lrv-u-margin-r-025 lrv-u-display-inline-block a-border-animate';

const {
	c_title,
} = o_card;

o_span_group.o_span_group_classes = 'lrv-u-flex lrv-u-flex-direction-column lrv-u-margin-t-050 ';

o_span_group.o_span_group_items = [
	c_span_new_arrival,
	c_span_critical_reviews
];

c_title.c_title_classes = 'u-order-n1 lrv-u-padding-t-050 lrv-u-font-size-16 u-font-size-18@desktop-xl u-order-n1';
c_title.c_title_link_classes = c_title.c_title_link_classes.replace( 'lrv-u-color-white ', '' );
c_title.c_title_link_classes = c_title.c_title_link_classes.replace( 'u-font-size-15 u-font-size-16@tablet', '' );
c_title.c_title_link_classes += ' u-color-black-pearl:hover';
o_card.c_span = false;
o_card.o_card_classes = 'u-width-245 u-width-200@tablet u-width-265@desktop-xl';
o_card.o_card_content_classes = 'lrv-u-flex lrv-u-flex-direction-column';
o_card.o_span_group = o_span_group;


module.exports = {
	...o_card,
	external_link_url,
};
