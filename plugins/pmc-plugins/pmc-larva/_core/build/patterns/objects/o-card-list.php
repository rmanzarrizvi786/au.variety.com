<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<ul class="o-card-list <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_card_list_classes ?? '' ); ?>">
	<?php foreach ( $o_card_list_items ?? [] as $item ) { ?>
		<li class="o-card-list__item <?php echo esc_attr( $o_card_list_item_classes ?? '' ); ?>">
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-card', $item, true ); ?>
		</li>
	<?php } ?>
</ul>
