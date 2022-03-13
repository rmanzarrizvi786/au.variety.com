<?php
$price = $price ? ' - ' . $price : '';
?>
<p><a data-pmc-sp-product="<?php echo esc_attr( $product->guid ); ?>" href="<?php echo esc_url( $product->url ); ?>" <?php do_action( 'pmc_do_render_buy_now_ga_tracking_attr', $variables ); ?> target="_blank" rel="nofollow"><?php echo wp_kses_post( $title . $price ); ?></a></p>
