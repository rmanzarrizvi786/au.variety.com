<div class="pmc-amp-buy-now">
	<?php
	if ( ! empty( $text ) ) {
		printf( '<div class="pmc-amp-buy-now-text"><strong>%s</strong></div>', esc_html( $text ) );
	}
	if ( ! empty( $price ) ) {
		printf( '<div class="pmc-amp-buy-now-price">%s</div>', esc_html( $price ) );
	}
	?>
	<div class="pmc-amp-buy-now-button" >
	<a rel="nofollow"
		href="<?php echo esc_url( $link ); ?>"
		<?php do_action( 'pmc_do_render_buy_now_ga_tracking_attr', $variables ); ?>
	>
		<strong><?php esc_html_e( 'Buy Now', 'pmc-buy-now' ); ?></strong>
	</a>
	</div>
</div>
