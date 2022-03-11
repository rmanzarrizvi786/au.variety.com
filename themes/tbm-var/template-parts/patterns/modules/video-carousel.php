<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="lrv-a-wrapper u-margin-lr-n050@mobile-max">
	<section class="video-carousel // <?php echo esc_attr( $video_carousel_classes ?? '' ); ?>">
		<div class="video-carousel__header lrv-a-wrapper // <?php echo esc_attr( $video_carousel_header_classes ?? '' ); ?>">
			<?php if ( ! empty( $o_more_from_heading ) ) { ?>
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_heading ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
			<?php } ?>

			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $view_all_link, true ); ?>
		</div>

		<div class="video__inner js-Flickity // <?php echo esc_attr( $video_inner_classes ?? '' ); ?>" data-flickity='{ "initialIndex": "1", "pageDots": false, "wrapAround": true }'>
			<?php foreach ( $video_items ?? [] as $item ) { ?>
				<div class="video__item js-Flickity-cell // <?php echo esc_attr( $video_item_classes ?? '' ); ?>">
					<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-video-card.php', $item, true ); ?>
				</div>
			<?php } ?>
		</div>

		<?php if ( ! empty( $o_more_link ) ) { ?>
			<div class="lrv-a-wrapper">
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
			</div>
		<?php } ?>
	</section>
</div>
