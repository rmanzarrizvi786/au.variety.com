const clonedeep = require( 'lodash.clonedeep' );

const c_heading = clonedeep( require( '../../components/c-heading/c-heading.accent-m' ) );
const c_span    = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
const c_dek = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' ) );

c_span.c_span_text = 'Podcasts';
c_span.c_span_classes = 'a-font-secondary-bold u-font-size-11 lrv-u-text-transform-uppercase lrv-u-margin-b-1@mobile-max lrv-u-display-block u-letter-spacing-012 lrv-u-margin-tb-075 u-margin-t-250@mobile-max u-line-height-1';
c_span.c_span_link_classes = 'lrv-a-unstyle-link u-color-brand-secondary-50:hover';

c_heading.c_heading_classes = 'a-font-accent-m lrv-u-font-size-32 lrv-u-padding-tb-00 lrv-u-border-t-3 u-line-height-1 u-letter-spacing-011-important lrv-u-padding-t-1 lrv-u-padding-b-025';

c_dek.c_dek_text = 'Japanese animation master Hayao Miyazaki, who turns 80 years old today.';
c_dek.c_dek_classes = 'lrv-u-font-family-secondary lrv-u-margin-tb-00 lrv-u-font-size-18@desktop';

module.exports = {
	o_sub_heading_classes: '',
	c_span: c_span,
	c_heading: c_heading,
	c_dek: c_dek,
};
