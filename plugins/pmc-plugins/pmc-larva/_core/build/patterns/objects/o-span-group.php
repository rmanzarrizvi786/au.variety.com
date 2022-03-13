<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="o-span-group <?php echo esc_attr( $o_span_group_classes ?? '' ); ?>">
	<?php foreach ( $o_span_group_items ?? [] as $item ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-span', $item, true ); ?>
	<?php } ?>
</div>
