<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="special-reports-carousel // <?php echo esc_attr( $special_reports_carousel_classes ?? '' ); ?>">
	<div class="lrv-a-wrapper">
		<?php if ( ! empty( $o_more_from_heading ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true ); ?>
		<?php } ?>
	</div>

	<div class="special-report__inner js-Flickity vip-slider // <?php echo esc_attr( $special_report_inner_classes ?? '' ); ?>" data-flickity='{ "initialIndex": "1", "pageDots": false, "wrapAround": true }'>
		<?php foreach ( $special_report_items ?? [] as $item ) { ?>
			<div class="special-report__item js-Flickity-cell // <?php echo esc_attr( $special_report_item_classes ?? '' ); ?>">
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-slide.php', $item, true ); ?>
			</div>
		<?php } ?>
	</div>

	<div class="lrv-a-wrapper">
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
	</div>
</section>
