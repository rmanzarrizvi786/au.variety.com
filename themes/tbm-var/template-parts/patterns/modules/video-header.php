<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="video-header // <?php echo esc_attr( $video_header_classes ?? '' ); ?>" <?php echo esc_attr( $video_header_data_attrs ?? '' ); ?>>
	<div class="lrv-a-wrapper // <?php echo esc_attr( $video_header_wrapper_classes ?? '' ); ?>">
		<div class="video-header__header // <?php echo esc_attr( $video_header_header_classes ?? '' ); ?>">
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>

			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/video-menu.php', $video_menu, true ); ?>

			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/video-menu-mobile.php', $video_menu_mobile, true ); ?>
		</div>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/video-showcase.php', $video_showcase, true ); ?>
	</div>
</div>
