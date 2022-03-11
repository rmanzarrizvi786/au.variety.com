const clonedeep = require( 'lodash.clonedeep' );
const post_content_image = clonedeep( require( './post-content-image.prototype' ) );

post_content_image.o_figure.o_figure_classes += ' lrv-a-glue-parent';
post_content_image.o_figure.c_figcaption.c_figcaption_classes += ' a-glue@desktop lrv-a-glue--b-0 lrv-a-glue--r-0 lrv-u-flex u-justify-content-end';
post_content_image.o_figure.c_figcaption.c_figcaption_credit_classes += ' u-word-break-break-word';
post_content_image.o_figure.c_figcaption.c_figcaption_inner = true;
post_content_image.o_figure.c_figcaption.c_figcaption_inner_classes += ' u-max-width-150@desktop u-max-width-200@desktop-xl a-featured-article-image-offsets__figcaption';

module.exports = post_content_image;
