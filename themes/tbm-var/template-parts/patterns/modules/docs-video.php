<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="docs-video__outer // <?php echo esc_attr( $docs_video_outer_classes ?? '' ); ?>">
	<div class="docs-video // <?php echo esc_attr( $docs_video_classes ?? '' ); ?>">

		<?php if ( ! empty( $c_heading ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
		<?php } ?>

		<div class="docs-video__inner // <?php echo esc_attr( $docs_video_inner_classes ?? '' ); ?>" data-video-showcase>

			<?php if ( ! empty( $o_video_card_top ) ) { ?>
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-video-card.php', $o_video_card_top, true ); ?>
			<?php } ?>

		</div>

		<?php if ( ! empty( $o_video_card_list ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-video-card-list.php', $o_video_card_list, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $o_more_link ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
		<?php } ?>

	</div>
</div>
