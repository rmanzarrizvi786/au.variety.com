<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="stories-slider // <?php echo esc_attr( $stories_slider_classes ?? '' ); ?>"
		<?php if ( ! empty( $stories_slider_id_attr ) ) { ?>
			id="<?php echo esc_attr( $stories_slider_id_attr ?? '' ); ?>"
		<?php } ?>
		<?php if ( ! empty( $stories_slider_offset_attr ) ) { ?>
			data-scrollto-offset-top="<?php echo esc_attr( $stories_slider_offset_attr ?? '' ); ?>"
		<?php } ?>
	>
	<div class="lrv-a-wrapper // <?php echo esc_attr( $stories_slider_wrapper_classes ?? '' ); ?>">
		<div class="stories-slider__inner-wrapper // <?php echo esc_attr( $special_reports_carousel_classes ?? '' ); ?>">
			<?php if ( ! empty( $heading ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $heading, true ); ?>
			<?php } ?>
			<div class="stories-slider__inner js-Flickity vip-slider // <?php echo esc_attr( $special_report_inner_classes ?? '' ); ?>">
				<?php foreach ( $stories_slider_items ?? [] as $item ) { ?>
					<div class="stories-slider__item js-Flickity-cell // <?php echo esc_attr( $special_report_item_classes ?? '' ); ?>">
						<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-card.php', $item, true ); ?>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</section>
