const clonedeep = require( 'lodash.clonedeep' );

const cta_banner = clonedeep( require( './cta-banner.prototype' ) );

cta_banner.c_tagline.c_tagline_classes = cta_banner.c_tagline.c_tagline_classes.replace( 'lrv-u-font-family-secondary', '' );
cta_banner.c_tagline.c_tagline_classes = cta_banner.c_tagline.c_tagline_classes.replace( 'lrv-u-text-transform-uppercase', '' );
cta_banner.c_tagline.c_tagline_classes += ' lrv-u-margin-tb-00 lrv-u-margin-b-050@mobile-max';

cta_banner.c_tagline.c_tagline_text = 'Introducing Variety Intelligence Platform';

cta_banner.c_button.c_button_text = 'Learn About VIP+';
cta_banner.c_button.c_button_classes = cta_banner.c_button.c_button_classes.replace( 'u-font-family-accent', '' );

cta_banner.cta_banner_classes += ' lrv-a-wrapper u-font-family-basic lrv-u-margin-tb-1';
cta_banner.cta_banner_classes = cta_banner.cta_banner_classes.replace( 'u-background-color-brand-secondary-vip', '' );
cta_banner.cta_banner_inner_classes += ' u-background-color-brand-secondary-vip u-box-shadow-menu';

module.exports = cta_banner;
