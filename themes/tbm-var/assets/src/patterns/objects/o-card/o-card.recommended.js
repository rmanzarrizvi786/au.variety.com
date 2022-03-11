const clonedeep = require( 'lodash.clonedeep' );

const o_card_prototype = require( './o-card.prototype' );
const o_card = clonedeep( o_card_prototype );

const {
	c_title,
	c_span,
} = o_card;

c_title.c_title_classes = c_title.c_title_classes.replace( 'u-order-n1', '' );
c_title.c_title_classes = c_title.c_title_classes.replace( 'lrv-u-padding-t-025', 'lrv-u-padding-t-050' );
c_title.c_title_classes = c_title.c_title_classes.replace( 'lrv-u-padding-t-050@tablet', 'u-padding-t-025@tablet' );
c_title.c_title_classes += ' u-line-height-120';
c_title.c_title_link_classes = c_title.c_title_link_classes.replace( 'u-font-size-16@tablet', 'lrv-u-font-size-14@tablet' );
c_title.c_title_link_classes = c_title.c_title_link_classes.replace( 'lrv-u-color-white', 'lrv-u-color-black' );
c_title.c_title_link_classes += ' u-color-brand-accent-80:hover';
c_title.c_title_text = 'Legalized Betting On Sports Hits The One-Year Mark: U.S. Media Bӏitz In It to Win It';

c_span.c_span_text = 'Film';
c_span.c_span_url = '#category';
c_span.c_span_link_classes = 'u-color-brand-secondary-60';
c_span.c_span_classes = c_span.c_span_classes.replace( 'lrv-u-font-size-10', 'lrv-u-font-size-12' );
c_span.c_span_classes = c_span.c_span_classes.replace( 'u-margin-t-025@tablet', 'u-margin-t-075@tablet' );
c_span.c_span_classes += ' lrv-u-text-transform-uppercase u-letter-spacing-001 u-margin-t-025';

module.exports = {
	...o_card
};
