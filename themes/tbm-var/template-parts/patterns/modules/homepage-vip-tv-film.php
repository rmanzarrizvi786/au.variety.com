<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="homepage-vip-tv-film // u-margin-t-125 <?php echo esc_attr( $homepage_vip_tv_film_classes ?? '' ); ?>">
	<div class="homepage-vip-tv-film__curation-and-list // lrv-u-height-100p <?php echo esc_attr( $homepage_vip_tv_classes ?? '' ); ?>">
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/vip-curated.php', $vip_curated, true ); ?>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/homepage-vertical-list.php', $homepage_vertical_list, true ); ?>
	</div>

	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/homepage-vertical-list.php', $homepage_vertical_list_horizontal, true ); ?>
</section>
