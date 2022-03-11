<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<article class="o-card <?php echo esc_attr( $o_card_classes ?? '' ); ?>">
	<?php if ( ! empty( $o_card_link_url ) ) { ?>
		<a tabindex="0" href="<?php echo esc_url( $o_card_link_url ?? '' ); ?>" class="<?php echo esc_attr( $o_card_link_classes ?? '' ); ?>">
	<?php } ?>

	<?php if ( ! empty( $o_card_image_wrap_classes ) ) { ?>
		<div class="o-card__image-wrap <?php echo esc_attr( $o_card_image_wrap_classes ?? '' ); ?>">
	<?php } ?>

	<?php if ( ! empty( $c_lazy_image ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $o_indicator ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-indicator.php', $o_indicator, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $o_card_image_wrap_classes ) ) { ?>
		</div>
	<?php } ?>
	<div class="o-card__content <?php echo esc_attr( $o_card_content_classes ?? '' ); ?>">
		<?php if ( ! empty( $c_critics_pick_label ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_critics_pick_label, true ); ?>
		<?php } ?>
		<?php if ( ! empty( $o_span_group ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-span-group.php', $o_span_group, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_span ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_title ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/components/c-title.php', $c_title, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_timestamp ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-timestamp.php', $c_timestamp, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_dek ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-dek.php', $c_dek, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_link ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_tagline ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true ); ?>
		<?php } ?>
	</div>

	<?php if ( ! empty( $o_card_link_url ) ) { ?>
	</a>
	<?php } ?>
	<?php if ( ! empty( $external_link_url ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/components/c-link-logo.php', $external_link_url, true ); ?>
	<?php } ?>

</article>
