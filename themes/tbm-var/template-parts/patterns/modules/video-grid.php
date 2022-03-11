<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="video-grid // <?php echo esc_attr( $video_grid_wrapper_classes ?? '' ); ?>">
	<div class="lrv-a-wrapper">
		<div class="lrv-a-grid <?php echo esc_attr( $video_grid_classes ?? '' ); ?>">
			<?php foreach ( $video_items ?? [] as $item ) { ?>
				<div class="lrv-a-grid-item"
					<?php
					if ( ! empty( $play_in_place ) ) {
						?>
						data-video-showcase <?php } ?>
				>
					<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-video-card.php', $item, true ); ?>
				</div>
			<?php } ?>
		</div>

		<?php if ( ! empty( $o_more_link ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
		<?php } ?>
	</div>
</div>
