<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="live-events__wrapper // <?php echo esc_attr( $live_events_wrapper_classes ?? '' ); ?>">
	<div class="live-events // <?php echo esc_attr( $live_events_classes ?? '' ); ?>">
		<div class="live-events__primary // <?php echo esc_attr( $live_events_primary_classes ?? '' ); ?>">
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true ); ?>

			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true ); ?>

			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-dek.php', $c_dek, true ); ?>

			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link_desktop, true ); ?>
		</div>

		<div class="live-events__secondary // <?php echo esc_attr( $live_events_secondary_classes ?? '' ); ?>">
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_secondary, true ); ?>

			<div class="live-events__images // <?php echo esc_attr( $live_events_images_classes ?? '' ); ?>">
				<?php foreach ( $live_events_images ?? [] as $item ) { ?>
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $item, true ); ?>
				<?php } ?>
			</div>

			<?php foreach ( $live_events_taglines ?? [] as $item ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $item, true ); ?>
			<?php } ?>

			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link_mobile, true ); ?>
		</div>
	</div>

	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/cxense-widget.php', $cxense_subscribe_widget, true ); ?>
</section>
