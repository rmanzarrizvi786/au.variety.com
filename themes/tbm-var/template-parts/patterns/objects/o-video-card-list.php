<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<ul class="o-video-card-list <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_video_card_list_classes ?? '' ); ?>">
	<?php foreach ( $o_video_card_list_items ?? [] as $item ) { ?>
		<li class="o-tease-list__item <?php echo esc_attr( $o_video_card_list_item_classes ?? '' ); ?>">
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-video-card.php', $item, true ); ?>
		</li>
	<?php } ?>
</ul>
