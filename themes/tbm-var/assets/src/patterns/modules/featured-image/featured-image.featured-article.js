const clonedeep = require( 'lodash.clonedeep' );

const featured_image = clonedeep( require( './featured-image.prototype' ) );

featured_image.o_figure.o_figure_classes += ' lrv-u-border-b-1 lrv-u-border-color-grey-light';

featured_image.o_figure.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-16x9';

featured_image.o_figure.o_figure_figcaption_outer = true;
featured_image.o_figure.o_figure_figcaption_outer_classes = 'lrv-u-padding-a-1';

featured_image.o_figure.c_figcaption.c_figcaption_caption_markup = 'Robert Patterson is an actor and internet phenomenon. He played someone in Twiglight and some other movies.';
featured_image.o_figure.c_figcaption.c_figcaption_caption_classes += ' lrv-u-font-family-secondary lrv-u-display-block lrv-u-margin-b-025';
featured_image.o_figure.c_figcaption.c_figcaption_classes += ' lrv-u-font-weight-light lrv-u-margin-b-025';
featured_image.o_figure.c_figcaption.c_figcaption_credit_classes += ' u-font-family-basic lrv-u-font-size-12 lrv-u-color-grey u-letter-spacing-001';

module.exports = featured_image;
