<?php
/**
 * Feed template of amazon products for amazon feed.
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @package pmc-store-products
 */

if ( empty( $product ) || empty( $product->url ) ) {
	return;
}
?>
<div data-itemtype="product"><a href="<?php echo esc_url( $product->url ); ?>"></a></div><?php echo wp_kses_post( strip_shortcodes( $product->description ) ); ?>
