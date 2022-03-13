<?php
if ( empty( $paywalled_post_ids ) ) {
	return;
}

$subscribe_url = apply_filters( 'pmc_subscription_paywall_posts_widget_subscribe_button_url', '' );

?>

<div class="paywall-plugin-posts hp-panel__widget">
	<div class="wwd-logo-black"><?php do_action( 'pmc_subscription_paywall_posts_widget_logo' ); ?></div>
	<div class="wwd-subscribe-now-slug"><?php esc_html_e( 'What Subscribers are Reading now:', 'pmc-subscription' ); ?></div>

	<ul>

		<?php foreach ( $paywalled_post_ids as $paywalled_post_id ) : ?>

			<li>
				<a href="<?php echo esc_url( get_permalink( $paywalled_post_id ) ); ?>" class="wwd-roadblocked-post"><?php echo esc_html( get_the_title( $paywalled_post_id ) ); ?></a>
			</li>

		<?php endforeach; ?>

	</ul>

	<?php if ( ! empty( $subscribe_url ) ) : ?>

		<a href="<?php echo esc_url( $subscribe_url ); ?>" class="wwd-subscribe-now-cta-button-link">
			<div class="wwd-subscribe-now-cta-button"><?php echo esc_html( apply_filters( 'pmc_paywall_posts_widget_button_text', __( 'SUBSCRIBE NOW', 'pmc-subscription' ) ) ); ?></div>
		</a>

	<?php endif; ?>
</div>
