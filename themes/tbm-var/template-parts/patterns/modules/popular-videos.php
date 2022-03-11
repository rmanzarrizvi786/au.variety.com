<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="popular-videos // <?php echo esc_attr( $popular_videos_classes ?? '' ); ?>">
	<?php foreach ( $popular_videos_items ?? [] as $item ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-popular-videos.php', $item, true ); ?>
	<?php } ?>
</section>
