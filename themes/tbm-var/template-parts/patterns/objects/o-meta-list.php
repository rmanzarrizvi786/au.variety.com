<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $o_meta_list_items ) ) { ?>
	<ul class="o-meta-list // <?php echo esc_attr( $o_meta_list_classes ?? '' ); ?>">
		<?php foreach ( $o_meta_list_items ?? [] as $item ) { ?>
			<li class="o-meta-list__item // <?php echo esc_attr( $o_meta_list_items_classes ?? '' ); ?>">
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/components/c-meta-item.php', $item, true ); ?>
			</li>
		<?php } ?>
	</ul>
<?php } ?>
