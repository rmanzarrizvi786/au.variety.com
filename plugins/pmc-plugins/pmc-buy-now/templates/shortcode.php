<p>
	<a class="pmc-buy-now-button" rel="nofollow"
		href="<?php echo esc_url( $link ); ?>"
		<?php do_action( 'pmc_do_render_buy_now_ga_tracking_attr', $variables ); ?>
		target="<?php echo esc_attr( $target ); ?>">
		<?php echo esc_html( $text ); ?>
		<?php
		if ( ! empty( $orig_price ) ) {
			echo ' ';
			?>
			<s><?php echo esc_html( $orig_price ); ?></s>
			<?php
		}
		echo ' ';
		?>
		<?php echo esc_html( $price ); ?>
	</a>
</p>
