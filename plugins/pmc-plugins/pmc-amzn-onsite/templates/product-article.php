<section class="pmc-amzn-onsite">
	<?php echo wp_kses_post( $product['display_title'] . $product['description'] ); ?>
	<p>
		<?php
		printf(
			'[buy-now url="%s" asin="%s" title="%s" button_type="amazon"/]',
			esc_url( $product['product_link'] ),
			esc_html( $product['product_id'] ),
			esc_html( \PMC::truncate( $product['title'], 50, 'ellipsis', true ) )
		);
		?>
	</p>
</section>
<?php if ( 'applenews' === $format ) { ?>
	<hr />
<?php } ?>
