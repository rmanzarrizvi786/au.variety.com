const clonedeep = require( 'lodash.clonedeep' );

const o_card = clonedeep( require( '../../objects/o-card/o-card.story' ) );
const stories_slider = clonedeep( require( '../stories-slider/stories-slider.prototype' ) );
const streamers_section_header = clonedeep( require( '../streamers-section-header/streamers-section-header.prototype' ) );
const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' );

const c_span_streamer = clonedeep( c_span_prototype );
const c_span_date = clonedeep( c_span_prototype );

c_span_streamer.c_span_text = 'Apple TV+';
c_span_streamer.c_span_classes += ' lrv-u-text-transform-uppercase u-font-size-11 a-font-secondary-bold u-letter-spacing-012';

c_span_date.c_span_text = 'Streams March 15';
c_span_date.c_span_classes = 'lrv-u-color-grey-dark lrv-u-text-transform-uppercase u-font-size-11 lrv-u-font-weight-bold lrv-u-font-family-secondary u-letter-spacing-012 lrv-u-margin-t-025';

o_card.o_span_group.o_span_group_items = [
	c_span_streamer,
	c_span_date,
]
o_card.external_link_url.c_link_logo_text = 'Remind Me';
o_card.external_link_url.c_link_logo_text_classes = 'lrv-u-margin-r-050 lrv-u-display-inline-block a-border-animate';
o_card.external_link_url.c_link_logo_screen_reader_text = 'Remind Me';
o_card.external_link_url.c_link_logo_svg = 'calendar';
o_card.external_link_url.c_link_logo_classes += ' js-AddToCalendar';
o_card.external_link_url.c_link_logo_calendar_attr = true;
o_card.external_link_url.data_start_attr = '';
o_card.external_link_url.data_title_attr = '';
o_card.external_link_url.data_location_attr = '';

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
	awards_contenders_classes: 'lrv-a-wrapper u-margin-b-250',
	streamers_section_header,
	stories_slider,
};
