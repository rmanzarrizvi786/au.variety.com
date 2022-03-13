<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="newsletter-cta">
	<div class="<?php echo esc_attr( $newsletter_cta_classes ?? '' ); ?>">
		<div class="lrv-u-text-align-center@mobile-max">
			<?php if ( ! empty( $c_logo ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-logo', $c_logo, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_heading ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-heading', $c_heading, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_tagline ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-tagline', $c_tagline, true ); ?>
			<?php } ?>

		</div>

		<?php if ( ! empty( $c_button ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-button', $c_button, true ); ?>
		<?php } ?>
	</div>
</div>
