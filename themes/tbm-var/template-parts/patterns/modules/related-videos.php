<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="related-videos // <?php echo esc_attr( $related_videos_classes ?? '' ); ?>">
	<?php if ( ! empty( $o_more_from_heading ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true ); ?>
	<?php } ?>

	<div class="related-videos__wrap // <?php echo esc_attr( $related_videos_wrap_classes ?? '' ); ?>">
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-video-card-list.php', $o_video_card_list, true ); ?>
	</div>
</div>
