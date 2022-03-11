<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="explore-all-events // <?php echo esc_attr( $explore_all_events_carousel_classes ?? '' ); ?>" data-collapsible="collapsed">
	<div class="lrv-a-wrapper // lrv-u-flex@tablet lrv-u-align-items-center lrv-u-justify-content-space-between lrv-u-margin-b-1@tablet lrv-u-padding-lr-2">
		<?php if ( ! empty( $o_more_from_heading ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true ); ?>
		<?php } ?>

		<div data-collapsible-toggle>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link_desktop, true ); ?>
		</div>
	</div>

	<div class="explore-all-events__inner // <?php echo esc_attr( $explore_all_events_inner_classes ?? '' ); ?>">
		<?php foreach ( $explore_all_events_items ?? [] as $item ) { ?>
			<div class="explore-all-events__item // <?php echo esc_attr( $explore_all_events_item_classes ?? '' ); ?>">
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-slide.php', $item, true ); ?>
			</div>
		<?php } ?>
	</div>

	<div class="explore-all-events__inner-hidden // <?php echo esc_attr( $explore_all_events_inner_hidden_classes ?? '' ); ?>" data-collapsible-panel>
		<?php foreach ( $explore_all_events_hidden_items ?? [] as $item ) { ?>
			<div class="explore-all-events__item // <?php echo esc_attr( $explore_all_events_item_hidden_classes ?? '' ); ?>">
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-slide.php', $item, true ); ?>
			</div>
		<?php } ?>
	</div>

	<div class="lrv-a-wrapper" data-collapsible-toggle>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
	</div>
</section>
