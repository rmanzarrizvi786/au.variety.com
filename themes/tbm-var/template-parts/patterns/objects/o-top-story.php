<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="o-top-story // <?php echo esc_attr( $o_top_story_classes ?? '' ); ?>">
	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true ); ?>

	<div class="o-top-story__inner // <?php echo esc_attr( $o_top_story_inner_classes ?? '' ); ?>">
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-indicator.php', $o_indicator, true ); ?>

		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-title.php', $c_title, true ); ?>

		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-dek.php', $c_dek, true ); ?>
	</div>
</div>
