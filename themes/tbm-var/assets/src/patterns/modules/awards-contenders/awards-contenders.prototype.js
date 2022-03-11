const clonedeep = require( 'lodash.clonedeep' );

const c_span = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
const o_card = clonedeep( require( '../../objects/o-card/o-card.story' ) );
const stories_slider = clonedeep( require( '../stories-slider/stories-slider.prototype' ) );
const streamers_section_header = clonedeep( require( '../streamers-section-header/streamers-section-header.prototype' ) );

c_span.c_span_text = 'Apple TV+';
c_span.c_span_classes += ' lrv-u-text-transform-uppercase u-font-size-11 a-font-secondary-bold u-letter-spacing-012 lrv-u-margin-t-050';

o_card.o_span_group = false;
o_card.c_span = clonedeep( c_span );

const o_card_first = clonedeep( o_card );
o_card.o_card_classes += ' lrv-u-margin-lr-050';
o_card_first.o_card_classes += ' lrv-u-margin-r-050';

stories_slider.stories_slider_classes = 'lrv-u-border-t-1 u-border-color-link-water';
stories_slider.heading = false;
stories_slider.stories_slider_items = [
	o_card_first,
	o_card,
	o_card,
	o_card,
	o_card,
];

module.exports = {
	awards_contenders_classes: 'lrv-a-wrapper lrv-u-margin-tb-150@mobile-max u-margin-tb-250@tablet',
	streamers_section_header,
	stories_slider,
};
