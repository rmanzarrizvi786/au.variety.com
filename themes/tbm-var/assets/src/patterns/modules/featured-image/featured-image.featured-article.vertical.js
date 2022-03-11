const clonedeep = require( 'lodash.clonedeep' );

const featured_image = clonedeep( require( './featured-image.featured-article' ) );

featured_image.o_figure.o_figure_classes = '';
featured_image.o_figure.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-3x4';

featured_image.o_figure.o_figure_figcaption_outer_classes += ' lrv-a-glue@tablet lrv-a-glue--b-0 lrv-a-glue--l-0 u-width-50p@tablet lrv-u-margin-l-1';

featured_image.o_figure.c_figcaption.c_figcaption_classes += ' a-border-triangle-before u-color-brand-secondary-30@tablet';

module.exports = featured_image;
