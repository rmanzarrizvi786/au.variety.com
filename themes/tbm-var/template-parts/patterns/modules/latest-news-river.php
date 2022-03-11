<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="latest-news-river // js-LatestNewsButton-ScrollDestination lrv-u-flex lrv-u-flex-direction-column <?php echo esc_attr( $latest_news_river_classes ?? '' ); ?>">

	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true ); ?>

	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-news-list.php', $o_tease_news_list_primary, true ); ?>

	<?php if ( ! empty( $cxense_subscribe_widget ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/cxense-widget.php', $cxense_subscribe_widget, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $o_tease_news_list_secondary ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-news-list.php', $o_tease_news_list_secondary, true ); ?>
	<?php } ?>

	<div class="lrv-u-flex lrv-u-justify-content-space-between lrv-u-padding-b-1 lrv-u-margin-t-auto">
		<?php if ( ! empty( $latest_news_river_is_paged ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link_previous, true ); ?>
		<?php } ?>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
	</div>
</section>
