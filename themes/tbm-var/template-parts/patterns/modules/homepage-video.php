<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="homepage-video__outer // lrv-a-wrapper a-wrapper-padding-unset@mobile-max <?php echo esc_attr( $homepage_video_outer_classes ?? '' ); ?>">
	<div class="homepage-video // <?php echo esc_attr( $homepage_video_classes ?? '' ); ?>">
		<div class="homepage-video__inner // <?php echo esc_attr( $homepage_video_inner_classes ?? '' ); ?>" data-video-showcase>
			<?php if ( ! empty( $c_heading ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
			<?php } ?>

			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/video-showcase.php', $video_showcase, true ); ?>

			<?php if ( ! empty( $o_more_link ) ) { ?>
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
			<?php } ?>
		</div>
	</div>
</div>
