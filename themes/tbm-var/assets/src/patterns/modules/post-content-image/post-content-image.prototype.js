const clonedeep = require( 'lodash.clonedeep' );
const post_content_image = clonedeep( require( '@penskemediacorp/larva-patterns/modules/post-content-image/post-content-image.prototype' ) );

post_content_image.o_figure.c_figcaption.c_figcaption_classes = 'lrv-u-font-size-12 lrv-u-font-family-secondary lrv-u-width-100p lrv-u-text-align-left';
post_content_image.o_figure.c_figcaption.c_figcaption_inner = true;
post_content_image.o_figure.c_figcaption.c_figcaption_inner_classes = 'lrv-u-padding-t-025 lrv-u-width-100p lrv-u-border-b-1 lrv-u-border-color-grey-light lrv-u-flex lrv-u-flex-direction-column lrv-u-padding-b-050';
post_content_image.o_figure.c_figcaption.c_figcaption_caption_classes += ' lrv-u-margin-t-050';
post_content_image.o_figure.c_figcaption.c_figcaption_credit_classes = 'lrv-u-color-grey u-font-family-basic';

module.exports = post_content_image;
