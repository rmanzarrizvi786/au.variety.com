<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<ul class="o-tease-list <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_tease_list_classes ?? '' ); ?>"
	<?php if ( ! empty( $o_tease_list_labelledby_attr ) ) { ?>
		aria-labelledby="<?php echo esc_attr( $o_tease_list_labelledby_attr ?? '' ); ?>"
	<?php } ?>
>
	<?php foreach ( $o_tease_list_items ?? [] as $item ) { ?>
		<li class="o-tease-list__item <?php echo esc_attr( $o_tease_list_item_classes ?? '' ); ?>">
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-tease.php', $item, true ); ?>
		</li>
	<?php } ?>
</ul>
