const clonedeep = require( 'lodash.clonedeep' );

const c_button_prototype = require( '@penskemediacorp/larva-patterns/components/c-button/c-button.prototype.js' );
const c_button = clonedeep( c_button_prototype );

c_button.c_button_text = 'Read the Report';
c_button.c_button_url = '#';
c_button.c_button_classes = 'lrv-u-background-color-brand-primary u-background-color-brand-primary-dark:hover lrv-u-color-white lrv-u-color-white:hover lrv-u-display-block lrv-u-padding-tb-025 lrv-u-text-align-center u-font-family-accent u-font-size-18 u-letter-spacing-001';

module.exports = {
	featured_chart_classes: 'u-max-width-300 lrv-u-margin-lr-auto',
  featured_chart_iframe_url: '//datawrapper.dwcdn.net/Y6cik/5/',
  featured_chart_iframe_height_attr: '727',
	c_button,
};

