<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<p class="buy-now // <?php echo esc_attr( $buy_now_classes ?? '' ); ?>">
	<a
		<?php if ( ! empty( $guid_attr ) ) { ?>
			data-pmc-sp-product="<?php echo esc_attr( $guid_attr ?? '' ); ?>"
		<?php } ?>
		class="pmc-buy-now-button // <?php echo esc_attr( $buy_now_inner_classes ?? '' ); ?>"
		rel="nofollow"
		href="<?php echo esc_url( $link_url ?? '' ); ?>"
		<?php do_action( 'pmc_do_render_buy_now_ga_tracking_attr', $variables ); ?>
		target="<?php echo esc_attr( $target_attr ?? '' ); ?>"
	>
		<span class="pmc-buy-now-button__content">
			<span class="pmc-buy-now-button__text">
				<?php echo esc_html( $buy_now_product_text ?? '' ); ?>
			</span>
			<span class="pmc-buy-now-button__price">
				<?php if ( ! empty( $orig_price_text ) ) { ?>
					<s><?php echo esc_html( $orig_price_text ?? '' ); ?></s>&nbsp;
				<?php } ?>
				<?php echo esc_html( $price_text ?? '' ); ?>
			</span>
		</span>
		<span class="pmc-buy-now-button__action // <?php echo esc_html( $buy_now_text_classes ?? '' ); ?>">
			<?php echo esc_html( $buy_now_text ?? '' ); ?>
		</span>
	</a>
</p>
