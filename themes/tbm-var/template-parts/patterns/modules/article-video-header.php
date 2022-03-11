<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="big-video // <?php echo esc_attr( $big_video_classes ?? '' ); ?>" data-video-showcase>
	<div class="wide-video__background // <?php echo esc_attr( $big_video_background_classes ?? '' ); ?>"></div>

	<div class="big-video__video u-position-relative // <?php echo esc_attr( $big_video_video_classes ?? '' ); ?>">
		<div id="cx-paywall"></div>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-video-card.php', $o_video_card, true ); ?>
	</div>

	<?php if ( ! empty( $is_article ) ) { ?>
		<div class="big-video__meta // <?php echo esc_attr( $big_video_meta_classes ?? '' ); ?>">
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-indicator.php', $o_indicator, true ); ?>

			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>

			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-timestamp.php', $c_timestamp, true ); ?>

			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/social-share.php', $social_share, true ); ?>
		</div>
	<?php } ?>
</section>
