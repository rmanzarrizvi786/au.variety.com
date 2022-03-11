<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="o-slide // <?php echo esc_attr( $o_slide_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_lazy_image ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $o_indicator ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-indicator.php', $o_indicator, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $c_title ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-title.php', $c_title, true ); ?>
	<?php } ?>

	<a class="o-slide__meta // <?php echo esc_attr( $o_slide_meta_classes ?? '' ); ?>" href="<?php echo esc_url( $o_slide_link_url ?? '' ); ?>">
		<?php if ( ! empty( $c_heading ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_timestamp ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-timestamp.php', $c_timestamp, true ); ?>
		<?php } ?>
	</a>
</div>
