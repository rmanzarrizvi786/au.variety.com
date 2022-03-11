<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="o-checkbox-input-list <?php echo esc_attr( $o_checkbox_input_list_classes ?? '' ); ?>">

	<?php if ( ! empty( $c_title ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-title.php', $c_title, true ); ?>
	<?php } ?>

	<ul class="<?php echo esc_attr( $o_checkbox_input_list_items_classes ?? '' ); ?>">
		<?php foreach ( $o_checkbox_input_list_items ?? [] as $item ) { ?>
			<li class="<?php echo esc_attr( $o_checkbox_input_list_item_classes ?? '' ); ?>">
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-checkbox-input.php', $item, true ); ?>
			</li>
		<?php } ?>
	</ul>

</section>
