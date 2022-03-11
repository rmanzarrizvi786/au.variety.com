<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="story-grid lrv-u-padding-tb-1 lrv-u-padding-tb-2@desktop // <?php echo esc_attr( $story_grid_classes ?? '' ); ?>">
	<ul class="<?php echo esc_attr( $story_grid_grid_classes ?? '' ); ?> // lrv-a-unstyle-list lrv-a-space-children-horizontal lrv-a-space-children--050 lrv-u-margin-b-050">
		<?php foreach ( $story_grid_story_cards ?? [] as $item ) { ?>
			<li>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/modules/story.php', $item, true ); ?>
			</li>
		<?php } ?>
	</ul>
</div>
