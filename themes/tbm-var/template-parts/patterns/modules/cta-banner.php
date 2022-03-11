<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<aside class="cta-banner // <?php echo esc_attr( $cta_banner_classes ?? '' ); ?>">
	<div class="cta-banner__inner <?php echo esc_attr( $cta_banner_inner_classes ?? '' ); ?>">
		<?php if ( ! empty( $c_tagline ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_button ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-button.php', $c_button, true ); ?>
		<?php } ?>
	</div>
</aside>
