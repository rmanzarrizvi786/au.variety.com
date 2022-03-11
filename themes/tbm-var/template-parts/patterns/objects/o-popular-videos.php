<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="lrv-a-wrapper">
	<div class="o-popular-videos // <?php echo esc_attr( $o_popular_videos_classes ?? '' ); ?>">
		<div class="o-popular-videos__inner // <?php echo esc_attr( $o_popular_videos_inner_classes ?? '' ); ?>">
			<div class="o-popular-videos__header // <?php echo esc_attr( $o_popular_videos_header_classes ?? '' ); ?>">
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>

				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
			</div>

			<div class="o-popular-videos__content // <?php echo esc_attr( $o_popular_videos_content_classes ?? '' ); ?>">
				<div class="o-popular-videos__primary // <?php echo esc_attr( $o_popular_videos_primary_classes ?? '' ); ?>">
					<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-video-card.php', $o_video_card_primary, true ); ?>
				</div>

				<div class="o-popular-videos__secondary // <?php echo esc_attr( $o_popular_videos_secondary_classes ?? '' ); ?>">
					<?php foreach ( $o_popular_videos_items ?? [] as $item ) { ?>
						<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-video-card.php', $item, true ); ?>
					<?php } ?>
				</div>
			</div>

			<?php if ( ! empty( $o_more_link_mobile ) ) { ?>
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link_mobile, true ); ?>
			<?php } ?>
		</div>
	</div>
</div>
