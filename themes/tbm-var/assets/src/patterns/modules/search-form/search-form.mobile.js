const clonedeep = require( 'lodash.clonedeep' );

const search_form_prototype = require( './search-form.prototype.js' );
const search_form = clonedeep( search_form_prototype );

search_form.search_form_classes = 'a-hidden@tablet lrv-u-flex lrv-u-width-100p u-margin-t-175@mobile-max u-margin-b-075@mobile-max u-padding-lr-3 u-padding-t-1@mobile-max';
search_form.search_form_input_label_classes = 'lrv-u-width-100p lrv-u-margin-r-050';
search_form.search_form_input_classes = 'a-placeholder-color-pale-sky-2@mobile-max a-reset-input a-reset-input--search lrv-u-border-a-0 u-border-b-1@mobile-max u-border-r-1@mobile-max lrv-u-border-r-1 lrv-u-font-family-secondary lrv-u-font-size-16 u-background-color-geyser u-color-pale-sky-2 lrv-u-width-100p';
search_form.search_form_submit_classes = 'a-reset-input lrv-u-border-a-0 lrv-u-color-white lrv-u-font-family-secondary lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-background-color-pale-sky-2 u-letter-spacing-2';
search_form.search_form_submit_text = 'Go';

module.exports = {
	...search_form,
};
