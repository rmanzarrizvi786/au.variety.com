const clonedeep = require( 'lodash.clonedeep' );

const o_figure_prototype = require( '@penskemediacorp/larva-patterns/objects/o-figure/o-figure.prototype.js' );
const o_figure = clonedeep( o_figure_prototype );

o_figure.o_figure_link_url = '';
o_figure.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-4x3';
o_figure.c_figcaption.c_figcaption_classes = 'lrv-u-font-size-12 lrv-u-flex lrv-u-flex-direction-column lrv-u-padding-tb-025';
o_figure.c_figcaption.c_figcaption_caption_classes += ' lrv-u-font-size-14@desktop';
o_figure.c_figcaption.c_figcaption_credit_classes = 'lrv-u-text-transform-uppercase lrv-u-color-grey';

module.exports = {
	o_figure: o_figure
};
