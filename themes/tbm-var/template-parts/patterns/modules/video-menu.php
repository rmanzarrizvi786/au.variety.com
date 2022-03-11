<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="video-menu // <?php echo esc_attr( $video_menu_classes ?? '' ); ?>">
	<div class="video-menu__inner // <?php echo esc_attr( $video_menu_inner_classes ?? '' ); ?>">
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav, true ); ?>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-drop-menu.php', $o_drop_menu, true ); ?>
	</div>
</div>
