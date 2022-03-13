<div style="text-align:center;">
	<?php echo wp_kses_post( $product['display_title'] ); ?>
	<div data-itemtype="product">
	<?php
		echo wp_kses_post( strip_shortcodes( $product['description'] ) );
		printf( '<a href="%s" target="_blank" rel="nofollow">Buy: %s %s</a>', esc_url( \PMC\EComm\Tracking::get_instance()->track( $product['product_link'] ) ), wp_kses_post( $product['title'] ), esc_html( $product['product_price'] ) );
	?>
	</a>
	</div>
</div>
