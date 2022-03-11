<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<<?php echo esc_attr( $list_type_name ?? '' ); ?>l class="list larva // <?php echo esc_attr( $list_classes ?? '' ); ?>">
	<?php foreach ( $list_items ?? [] as $item ) { ?>
		<li class="list__item larva // <?php echo esc_attr( $list_item_classes ?? '' ); ?>">
			<?php echo wp_kses_post( $item['list_markup'] ?? '' ); ?>

			<?php if ( $item['list_items'] ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/modules/list.php', $item, true ); ?>
			<?php } ?>
		</li>
	<?php } ?>
</<?php echo esc_attr( $list_type_name ?? '' ); ?>l>
