<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="related-article // <?php echo esc_attr( $related_article_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_lazy_image ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true ); ?>
	<?php } ?>

	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
</div>
