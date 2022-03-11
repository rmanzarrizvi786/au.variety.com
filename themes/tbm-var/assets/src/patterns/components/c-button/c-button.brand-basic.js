const clonedeep = require( 'lodash.clonedeep' );

const c_button_protoype = require( '@penskemediacorp/larva-patterns/components/c-button/c-button.brand-basic' );
const c_button = clonedeep( c_button_protoype );

c_button.c_button_url = '#';
c_button.c_button_classes = c_button.c_button_classes.replace( 'lrv-u-background-color-brand-primary', 'u-background-color-brand-secondary' );
c_button.c_button_classes += ' lrv-u-border-a-1 u-border-color-brand-secondary-30 u-color-brand-accent-20:hover lrv-u-font-weight-bold lrv-u-font-family-secondary lrv-u-font-size-12 u-letter-spacing-2 lrv-u-text-transform-uppercase';

module.exports = c_button;
