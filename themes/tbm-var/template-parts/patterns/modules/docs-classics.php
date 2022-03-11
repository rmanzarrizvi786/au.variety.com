<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="docs-classics // js-LatestNewsButton-ScrollDestination lrv-u-flex lrv-u-flex-direction-column <?php echo esc_attr( $latest_news_river_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_heading ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
	<?php } ?>
	<?php if ( ! empty( $c_tagline ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true ); ?>
	<?php } ?>
	<div class="lrv-a-grid lrv-a-cols4@tablet u-grid-gap-175 u-grid-gap-125@tablet u-margin-b-250 u-align-items-stretch">
		<?php foreach ( $classics_row_items ?? [] as $item ) { ?>
			<div class="classics-row__item // <?php echo esc_attr( $classics_row_item_classes ?? '' ); ?>">
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-card.php', $item, true ); ?>
			</div>
		<?php } ?>
	</div>
</section>
