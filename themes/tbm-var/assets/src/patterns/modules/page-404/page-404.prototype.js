const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' );
const c_heading = clonedeep( c_heading_prototype );

const c_title_prototype = require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype' );
const c_title = clonedeep( c_title_prototype );

const c_tagline_prototype = require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' );
const c_tagline = clonedeep( c_tagline_prototype );

const c_button_prototype = require( '../../components/c-button/c-button.brand-basic' );
const c_button = clonedeep( c_button_prototype );

c_heading.c_heading_text = '404';
c_heading.c_heading_classes = 'lrv-u-font-size-86 lrv-u-font-family-primary lrv-u-font-weight-bold lrv-u-line-height-small u-letter-spacing-012';

c_title.c_title_text = 'Page Not Found';
c_title.c_title_classes = 'lrv-u-font-family-secondary lrv-u-font-size-20 lrv-u-font-weight-normal lrv-u-text-transform-uppercase';
c_title.c_title_url = false;

c_tagline.c_tagline_text = 'Sorry, the page you were looking for cannot be found.';
c_tagline.c_tagline_classes = 'lrv-u-margin-tb-2 u-font-family-body lrv-u-font-size-32';

c_button.c_button_classes = c_button.c_button_classes.replace( ' u-background-color-brand-secondary ', ' ' );
c_button.c_button_text = 'Return to Homepage';
c_button.c_button_url = '/';

module.exports = {
	c_heading: c_heading,
	c_title: c_title,
	c_tagline: c_tagline,
	c_button: c_button,
};
