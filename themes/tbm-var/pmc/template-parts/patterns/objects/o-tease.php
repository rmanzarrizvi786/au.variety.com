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
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_heading ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_title ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-title.php', $c_title, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_tagline ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_button ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-button.php', $c_button, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_byline ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-byline.php', $c_byline, true ); ?>
			<?php } ?>
		</div>

		<?php if ( ! empty( $c_lazy_image ) ) { ?>
		<div class="o-tease__secondary <?php echo esc_attr( $o_tease_secondary_classes ?? '' ); ?>">
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true ); ?>
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
