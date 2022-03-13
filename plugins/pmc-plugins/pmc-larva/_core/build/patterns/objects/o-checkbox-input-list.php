<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="o-checkbox-input-list <?php echo esc_attr( $o_checkbox_input_list_classes ?? '' ); ?>">

	<?php if ( ! empty( $c_title ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-title', $c_title, true ); ?>
	<?php } ?>

	<ul class="<?php echo esc_attr( $o_checkbox_input_list_items_classes ?? '' ); ?>">
		<?php foreach ( $o_checkbox_input_list_items ?? [] as $item ) { ?>
			<li class="<?php echo esc_attr( $o_checkbox_input_list_item_classes ?? '' ); ?>">
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-checkbox-input', $item, true ); ?>
			</li>
		<?php } ?>
	</ul>

</section>
