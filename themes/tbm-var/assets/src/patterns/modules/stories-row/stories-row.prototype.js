
const clonedeep = require( 'lodash.clonedeep' );

const o_card_prototype = require( '../../objects/o-card/o-card.prototype' );
const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' );
const c_span = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
const c_dek = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' ) );
const o_card = clonedeep( o_card_prototype );
const large_story = clonedeep( o_card_prototype );

c_span.c_span_text = 'Streaming Ideas';
c_span.c_span_classes = 'a-font-secondary-bold lrv-u-font-size-10 lrv-u-text-transform-uppercase lrv-u-margin-b-1@mobile-max lrv-u-display-block u-letter-spacing-012 a-hidden@tablet';
c_span.c_span_link_classes = 'lrv-a-unstyle-link u-color-brand-secondary-50:hover';

large_story.o_card_classes = 'lrv-u-flex lrv-u-flex-direction-column@mobile-max lrv-u-margin-b-125 lrv-u-margin-b-2@mobile-max';
large_story.o_card_content_classes += ' u-order-n1@tablet lrv-u-flex-grow-1 lrv-u-margin-r-2@desktop-xl lrv-u-margin-r-125 lrv-u-margin-r-00@mobile-max lrv-u-margin-t-1@mobile-max';
large_story.c_lazy_image.c_lazy_image_classes = 'u-width-60p@tablet lrv-u-flex-shrink-0';

large_story.c_span.c_span_text = 'Streaming Ideas';
large_story.c_span.c_span_classes = 'a-font-secondary-bold lrv-u-font-size-10 lrv-u-text-transform-uppercase lrv-u-margin-b-125 u-letter-spacing-012 a-hidden@mobile-max';
large_story.c_span.c_span_link_classes = 'lrv-a-unstyle-link u-color-brand-secondary-50:hover';

large_story.c_title.c_title_text = 'The 10 Best Studio Ghibli Movies';
large_story.c_title.c_title_classes = 'a-font-primary-regular u-font-size-48@desktop-xl u-font-size-42@tablet lrv-u-font-size-36 lrv-u-line-height-small lrv-u-margin-a-00';
large_story.c_title.c_title_link_classes = 'lrv-a-unstyle-link u-color-brand-secondary-50:hover';

large_story.c_dek = c_dek;
large_story.c_dek.c_dek_text = 'Japanese animation master Hayao Miyazaki, who turns 80 years old today.';
large_story.c_dek.c_dek_classes = 'lrv-u-font-family-secondary lrv-u-font-size-18@desktop-xl lrv-u-margin-t-1 lrv-u-margin-t-050@mobile-max lrv-u-margin-b-00';

large_story.c_link = clonedeep( c_link_prototype );
large_story.c_link.c_link_text = 'By Pat Saperstein';
large_story.c_link.c_link_classes = 'a-font-secondary-bold lrv-u-font-size-14 u-color-black-pearl u-color-brand-accent-80:hover lrv-u-margin-t-1 lrv-u-margin-b-00';
large_story.c_link.c_link_url = '#';

o_card.o_card_classes = 'lrv-u-flex lrv-u-flex-direction-column u-border-t-1@mobile-max u-padding-t-1@mobile-max u-border-color-loblolly-grey';

o_card.c_span = false;
o_card.c_title.c_title_text = '19 Shades of Black and White: Movies to Watch After ‘Mank’';
o_card.c_title.c_title_classes = 'a-font-secondary-bold u-font-size-21@desktop-xl lrv-u-font-size-18@mobile-max lrv-u-font-size-16 lrv-u-margin-t-075 lrv-u-margin-t-050@mobile-max u-color-brand-secondary-50:hover';
o_card.c_title.c_title_link_classes = 'lrv-a-unstyle-link';

o_card.c_link = clonedeep( c_link_prototype );
o_card.c_link.c_link_text = 'By Pat Saperstein';
o_card.c_link.c_link_classes = 'a-font-secondary-bold lrv-u-font-size-14 lrv-u-margin-t-125 u-color-black-pearl u-color-brand-accent-80:hover';
o_card.c_link.c_link_url = '#';

const stories_row_items = [
	o_card,
	o_card,
	o_card,
	o_card,
];

module.exports = {
	stories_row_classes: 'lrv-a-wrapper lrv-u-margin-tb-150@mobile-max u-margin-t-250@tablet u-margin-b-350@tablet',
	stories_row_wrapper_classes: 'lrv-u-border-t-3 lrv-u-padding-t-2 lrv-u-padding-t-075@mobile-max',
	stories_row_item_classes: 'u-height-100p@tablet',
	c_span,
	large_story,
	stories_row_items,
};
