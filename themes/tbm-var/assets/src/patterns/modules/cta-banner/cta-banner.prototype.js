const clonedeep = require( 'lodash.clonedeep' );
const c_button = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-button/c-button.prototype' ) );
const c_tagline = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' ) );

c_tagline.c_tagline_text = 'You have been granted exclusive access to this subscriber-only article.';
c_tagline.c_tagline_classes += ' lrv-u-text-transform-uppercase lrv-u-font-family-secondary lrv-u-font-size-16 lrv-u-font-size-18@desktop';

// Note: ideally this would be the brand-basic c-button variation with variety-vip tokens.
c_button.c_button_classes += ' lrv-u-padding-tb-050 lrv-u-padding-lr-1 u-background-color-brand-primary-vip lrv-u-color-white lrv-u-color-white:hover u-font-family-accent u-letter-spacing-001 u-font-size-15 u-margin-lr-075';
c_button.c_button_text = 'Unlock All VIP+ Content Now';

module.exports = {
	cta_banner_classes: 'u-background-color-brand-secondary-vip lrv-u-margin-b-1 lrv-u-text-align-center lrv-u-color-white',
	cta_banner_inner_classes: 'lrv-a-wrapper lrv-u-flex lrv-u-padding-a-1 lrv-u-flex-wrap-wrap lrv-u-align-items-center lrv-u-justify-content-center',
	c_tagline,
	c_button,
}
