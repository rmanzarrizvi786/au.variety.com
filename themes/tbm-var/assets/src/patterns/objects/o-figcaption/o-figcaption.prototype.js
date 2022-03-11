const clonedeep = require( 'lodash.clonedeep' );

const c_figcaption_prototype = require( '@penskemediacorp/larva-patterns/components/c-figcaption/c-figcaption.prototype' );
const c_figcaption = clonedeep( c_figcaption_prototype );

c_figcaption.c_figcaption_classes = 'lrv-u-flex lrv-u-flex-direction-column lrv-u-margin-t-050 lrv-u-padding-b-1';
c_figcaption.c_figcaption_caption_markup = 'Dano’s casting comes after Jonah Hill turning down an offer to join the cast. Insiders believe WB already had an offer ready to go out to Dano once Hill passed on the role.';
c_figcaption.c_figcaption_caption_classes = 'lrv-u-font-size-12 lrv-u-font-size-14@tablet u-color-dusty-grey';
c_figcaption.c_figcaption_credit_text = 'Photo: Getty Images/Matt LaFleur';
c_figcaption.c_figcaption_credit_classes = 'lrv-u-font-size-10 u-font-size-11@tablet u-letter-spacing-005 u-color-brand-secondary-80 u-font-family-basic lrv-u-line-height-large';

module.exports = {
	c_figcaption,
};
