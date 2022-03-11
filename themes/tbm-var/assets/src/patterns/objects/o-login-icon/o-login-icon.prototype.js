const clonedeep = require( 'lodash.clonedeep' );

const o_icon_button_prototype = require( '@penskemediacorp/larva-patterns/objects/o-icon-button/o-icon-button.prototype' );
const o_icon_button = clonedeep( o_icon_button_prototype );

o_icon_button.o_icon_button_url = true;
o_icon_button.o_button_url = '/digital-subscriber-access/#r=/print-plus/';
o_icon_button.o_icon_button_classes = 'lrv-u-align-items-center lrv-u-border-a-0 lrv-u-flex o-icon-button u-min-height-40 lrv-u-padding-lr-1 lrv-u-background-color-transparent lrv-u-color-white a-hidden@tablet u-margin-r-n050';
o_icon_button.c_icon.c_icon_name = 'login-filled';
o_icon_button.c_icon.c_icon_classes = 'lrv-u-display-block u-width-20 u-height-20 u-color-brand-primary-40:hover a-hidden@tablet';
o_icon_button.c_span.c_span_classes = ' a-hidden@mobile-max lrv-u-border-a-1 lrv-u-display-block lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-font-family-secondary lrv-u-padding-tb-050 lrv-u-padding-lr-050 lrv-u-text-align-center lrv-u-text-transform-uppercase u-letter-spacing-2 u-border-color-brand-secondary-30';
o_icon_button.o_icon_button_screen_reader_text = 'Click to Login';
o_icon_button.c_span.c_span_text = 'Login';

module.exports = {
	o_icon_button,
};
