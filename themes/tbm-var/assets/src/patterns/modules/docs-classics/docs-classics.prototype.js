const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' ) );
const c_heading = clonedeep( c_heading_prototype );
const c_tagline_prototype = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' ) );
const c_tagline = clonedeep( c_tagline_prototype );
const o_card_prototype = require( '../../objects/o-card/o-card.prototype' );
const o_card = clonedeep( o_card_prototype );
const c_dek_prototype = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' ) );
const c_dek = clonedeep( c_dek_prototype );

c_heading.c_heading_classes = 'a-font-accent-m lrv-u-padding-t-050 lrv-u-padding-b-1 lrv-u-line-height-small u-letter-spacing-015-important u-letter-spacing-025@desktop-xl';
c_heading.c_heading_text = 'Documentary Classics';

c_tagline.c_tagline_classes = 'a-font-secondary-regular-l lrv-u-margin-t-025 u-margin-b-175';
c_tagline.c_tagline_text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et';

c_dek.c_dek_text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
c_dek.c_dek_classes = 'lrv-u-color-white a-font-secondary-regular-l lrv-u-font-size-14 u-line-height-120 u-font-size-16@desktop lrv-u-margin-tb-050';

o_card.o_card_classes = '';
o_card.c_span = false;
o_card.c_dek = c_dek;
o_card.c_tagline = false;
o_card.c_title.c_title_classes = '';
o_card.c_title.c_title_text = "'Paris is Burning'";
o_card.c_title.c_title_link_classes = 'lrv-u-color-white u-font-size-16 u-font-size-21@desktop a-font-secondary-bold-m lrv-u-display-block u-line-height-130';
o_card.o_card_content_classes += ' lrv-u-padding-a-075';

const classics_row_items = [
	o_card,
	o_card,
	o_card,
	o_card,
];

module.exports = {
	latest_news_river_classes: 'u-margin-t-2@tablet lrv-u-border-t-3 u-border-color-picked-bluewood lrv-u-background-color-white',
	c_heading,
	classics_row_items,
	classics_row_item_classes: 'u-background-color-black-pearl u-box-shadow-menu',
};
