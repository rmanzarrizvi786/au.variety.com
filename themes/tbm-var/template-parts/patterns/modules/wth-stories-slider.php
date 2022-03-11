<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="wth-stories-slider // <?php echo esc_attr( $wth_stories_slider_classes ?? '' ); ?>"
		<?php if ( ! empty( $wth_stories_slider_id_attr ) ) { ?>
			id="<?php echo esc_attr( $wth_stories_slider_id_attr ?? '' ); ?>"
		<?php } ?>
		<?php if ( ! empty( $wth_stories_slider_offset_attr ) ) { ?>
			data-scrollto-offset-top="<?php echo esc_attr( $wth_stories_slider_offset_attr ?? '' ); ?>"
		<?php } ?>
	>
	<div class="lrv-a-wrapper // <?php echo esc_attr( $wth_stories_slider_wrapper_classes ?? '' ); ?>">
		<div class="wth-stories-slider__inner-wrapper // <?php echo esc_attr( $special_reports_carousel_classes ?? '' ); ?>">
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-sub-heading.php', $o_sub_heading, true ); ?>
			<div class="wth-stories-slider__inner js-Flickity vip-slider // <?php echo esc_attr( $special_report_inner_classes ?? '' ); ?>">
				<?php foreach ( $wth_stories_slider_items ?? [] as $item ) { ?>
					<div class="wth-stories-slider__item js-Flickity-cell // <?php echo esc_attr( $special_report_item_classes ?? '' ); ?>">
						<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-card.php', $item, true ); ?>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</section>
