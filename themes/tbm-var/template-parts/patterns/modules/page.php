<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="page // <?php echo esc_attr( $page_classes ?? '' ); ?>">
	<div class="page__inner <?php echo esc_attr( $page_inner_classes ?? '' ); ?>">
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/article-title.php', $article_title, true ); ?>
		<div class="page__content <?php echo esc_attr( $page_content_classes ?? '' ); ?>">
			<?php echo wp_kses_post( $page_markup ?? '' ); ?>
		</div>
	</div>
</div>
