<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<ul class="o-tease-news-list <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_tease_list_classes ?? '' ); ?>"
	<?php if ( ! empty( $o_tease_list_id_attr ) ) { ?>
		id="<?php echo esc_attr( $o_tease_list_id_attr ?? '' ); ?>"
	<?php } ?>>
	<?php foreach ( $o_tease_list_items ?? [] as $item ) { ?>
		<li class="o-tease-list__item <?php echo esc_attr( $o_tease_list_item_classes ?? '' ); ?>">
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-news.php', $item, true ); ?>
		</li>
	<?php } ?>
</ul>
