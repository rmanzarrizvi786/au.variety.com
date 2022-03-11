<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<li class="author-details-item // <?php echo esc_attr( $author_details_item_classes ?? '' ); ?>">
	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link, true ); ?>

	<?php if ( ! empty( $c_timestamp ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-timestamp.php', $c_timestamp, true ); ?>
	<?php } ?>
</li>
