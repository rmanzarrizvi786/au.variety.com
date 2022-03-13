<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $o_card_tag_text ) ) { ?>
<<?php echo esc_html( $o_card_tag_text ?? '' ); ?> class="o-card <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_card_classes ?? '' ); ?>">
<?php } else { ?>
<article class="o-card <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_card_classes ?? '' ); ?>">
<?php } ?>

	<?php if ( ! empty( $o_card_link_url ) ) { ?>
		<a tabindex="0" href="<?php echo esc_url( $o_card_link_url ?? '' ); ?>" class="<?php echo esc_attr( $o_card_link_classes ?? '' ); ?>">
	<?php } ?>

	<?php if ( ! empty( $o_card_image_wrap_classes ) ) { ?>
	<div class="o-card__image-wrap <?php echo esc_attr( $o_card_image_wrap_classes ?? '' ); ?>">
		<?php } ?>

		<?php if ( ! empty( $c_lazy_image ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-lazy-image', $c_lazy_image, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $o_indicator ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-indicator', $o_indicator, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $o_card_image_wrap_classes ) ) { ?>
	</div>
	<?php } ?>

	<div class="o-card__content <?php echo esc_attr( $o_card_content_classes ?? '' ); ?>">
		<?php if ( ! empty( $o_span_group ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-span-group', $o_span_group, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_span ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-span', $c_span, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_title ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-title', $c_title, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_timestamp ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-timestamp', $c_timestamp, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_dek ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-dek', $c_dek, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_tagline ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-tagline', $c_tagline, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $o_author ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-author', $o_author, true ); ?>
		<?php } ?>
	</div>

	<?php if ( ! empty( $o_card_link_url ) ) { ?>
	</a>
	<?php } ?>

<?php if ( ! empty( $o_card_tag_text ) ) { ?>
</<?php echo esc_html( $o_card_tag_text ?? '' ); ?>>
<?php } else { ?>
</article>
<?php } ?>
