<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="issue-item // <?php echo esc_attr( $issue_item_classes ?? '' ); ?>">
	<a href="<?php echo esc_url( $issue_item_url ?? '' ); ?>" class="<?php echo esc_attr( $issue_item_link_classes ?? '' ); ?>">
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true ); ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true ); ?>
	</a>
</div>
