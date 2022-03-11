<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="o-span-group <?php echo esc_attr( $o_span_group_classes ?? '' ); ?>">
	<?php foreach ( $o_span_group_items ?? [] as $item ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $item, true ); ?>
	<?php } ?>
</div>
