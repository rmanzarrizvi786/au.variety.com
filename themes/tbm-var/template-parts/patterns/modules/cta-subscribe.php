<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<aside class="cta-subscribe // <?php echo esc_attr( $cta_subscribe_classes ?? '' ); ?>">
	<?php if ( ! empty( $cxense_article_end_subscribe_widget ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/cxense-widget.php', $cxense_article_end_subscribe_widget, true ); ?>
	<?php } ?>
</aside>
