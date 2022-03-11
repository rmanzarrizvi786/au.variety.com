<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<ul class="issue-item-list // <?php echo esc_attr( $issue_item_list_classes ?? '' ); ?> lrv-a-unstyle-list">
	<?php foreach ( $issue_item_list ?? [] as $item ) { ?>
		<li>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/issue-item.php', $item, true ); ?>
		</li>
	<?php } ?>
</ul>
