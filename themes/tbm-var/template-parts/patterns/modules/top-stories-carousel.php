<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="top-stories-carousel // <?php echo esc_attr( $top_stories_carousel_classes ?? '' ); ?>">

	<?php if ( ! empty( $cxense_carousel_widget ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/cxense-widget.php', $cxense_carousel_widget, true ); ?>
	<?php } ?>

	<div class="top-stories-carousel-flickity-outer js-Flickity vip-slider // <?php echo esc_attr( $top_stories_carousel_flickity_outer_classes ?? '' ); ?>" data-flickity='{ "pageDots": true, "wrapAround": true, "autoPlay": 3000 }'>
		<?php foreach ( $top_stories_carousel ?? [] as $item ) { ?>
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-top-story.php', $item, true ); ?>
		<?php } ?>
	</div>

</section>


