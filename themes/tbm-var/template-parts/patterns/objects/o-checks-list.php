<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $o_checks_list_text_items ) ) { ?>
  <ul class="o-checks-list // <?php echo esc_attr( $o_checks_list_classes ?? '' ); ?>">
		<?php foreach ( $o_checks_list_text_items ?? [] as $item ) { ?>
			<li class="o-checks-list__item // <?php echo esc_attr( $o_checks_list_items_classes ?? '' ); ?>">
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-checks-list-item.php', $item, true ); ?>
			</li>
		<?php } ?>
	</ul>
<?php } ?>
