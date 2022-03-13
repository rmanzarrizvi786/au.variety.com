<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $is_single ) ) { ?>
	<div class="o-tease <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_tease_classes ?? '' ); ?>" <?php echo esc_attr( $o_tease_data_attributes ?? '' ); ?>>
<?php } else { ?>
	<article class="o-tease <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_tease_classes ?? '' ); ?>" <?php echo esc_attr( $o_tease_data_attributes ?? '' ); ?>>
<?php } ?>

	<?php if ( ! empty( $o_tease_url ) ) { ?>
		<a href="<?php echo esc_url( $o_tease_url ?? '' ); ?>" class="<?php echo esc_attr( $o_tease_link_classes ?? '' ); ?>">
	<?php } ?>
		<div class="o-tease__primary <?php echo esc_attr( $o_tease_primary_classes ?? '' ); ?>">
			<?php if ( ! empty( $c_span ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-span', $c_span, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_heading ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-heading', $c_heading, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_title ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-title', $c_title, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_tagline ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-tagline', $c_tagline, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_button ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-button', $c_button, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_byline ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-byline', $c_byline, true ); ?>
			<?php } ?>
		</div>

		<?php if ( ! empty( $c_lazy_image ) ) { ?>
		<div class="o-tease__secondary <?php echo esc_attr( $o_tease_secondary_classes ?? '' ); ?>">
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-lazy-image', $c_lazy_image, true ); ?>
		</div>
		<?php } ?>

	<?php if ( ! empty( $o_tease_url ) ) { ?>
		</a>
	<?php } ?>

<?php if ( ! empty( $is_single ) ) { ?>
	</div>
<?php } else { ?>
	</article>
<?php } ?>
