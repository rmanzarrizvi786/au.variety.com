<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="newsletter-cta">
	<div class="<?php echo esc_attr( $newsletter_cta_classes ?? '' ); ?>">
		<div class="lrv-u-text-align-center@mobile-max">
			<?php if ( ! empty( $c_logo ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-logo.php', $c_logo, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_heading ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_tagline ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true ); ?>
			<?php } ?>

		</div>

		<?php if ( ! empty( $c_button ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-button.php', $c_button, true ); ?>
		<?php } ?>
	</div>
</div>
