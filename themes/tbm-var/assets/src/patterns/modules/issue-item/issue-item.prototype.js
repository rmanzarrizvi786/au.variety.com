const clonedeep = require( 'lodash.clonedeep' );

const c_lazy_image = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype.js' ));

const c_span = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype.js' ) );

c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-3x4';

c_span.c_title_text = 'Debra and Leon Black';
c_span.c_span_classes = 'lrv-u-padding-t-1';
c_span.c_span_url = false;

module.exports = {
  issue_item_classes: 'lrv-a-grid-item issue-date lrv-u-font-weight-bold lrv-u-font-size-16 lrv-u-font-size-14@mobile-max lrv-u-font-family-secondary u-letter-spacing-025 u-border-color-brand-secondary-40 lrv-u-border-b-1 lrv-u-padding-b-1',
  issue_item_url: '#',
  issue_item_link_classes: 'access-digital-issue lrv-u-color-black u-color-black:hover',
  c_lazy_image: c_lazy_image,
  c_span: c_span,
}