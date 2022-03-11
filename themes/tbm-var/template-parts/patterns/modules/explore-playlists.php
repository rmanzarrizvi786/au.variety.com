<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="explore-playlists // <?php echo esc_attr( $explore_playlists_classes ?? '' ); ?>">
	<div class="lrv-a-wrapper // <?php echo esc_attr( $explore_playlists_wrapper_classes ?? '' ); ?>">
		<div class="explore-playlists__inner-wrapper // <?php echo esc_attr( $special_reports_carousel_classes ?? '' ); ?>">
			<?php if ( ! empty( $c_heading ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
			<?php } ?>

			<div class="explore-playlists__inner js-Flickity vip-slider // <?php echo esc_attr( $special_report_inner_classes ?? '' ); ?>">
				<?php foreach ( $explore_playlists_items ?? [] as $item ) { ?>
					<div class="explore-playlists__item js-Flickity-cell // <?php echo esc_attr( $special_report_item_classes ?? '' ); ?>">
						<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-card.php', $item, true ); ?>
					</div>
				<?php } ?>
			</div>
		</div>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
	</div>
</section>
