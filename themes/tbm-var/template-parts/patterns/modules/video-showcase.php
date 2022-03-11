<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="video_header__videos // <?php echo esc_attr( $video_header_videos_classes ?? '' ); ?>">
	<div class="video-header__article-player // <?php echo esc_attr( $video_header_single_player_classes ?? '' ); ?>" <?php echo esc_attr( $video_header_data_attrs ?? '' ); ?>>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-video-card.php', $o_video_card, true ); ?>
	</div>

	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/related-videos.php', $related_videos, true ); ?>
</div>
