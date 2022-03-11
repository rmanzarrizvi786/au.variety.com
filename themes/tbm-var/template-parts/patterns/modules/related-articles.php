<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="related-articles__outer // <?php echo esc_attr( $related_articles_outer_classes ?? '' ); ?>">
	<div class="related-articles // <?php echo esc_attr( $related_articles_classes ?? '' ); ?>">
		<div class="related-articles__heading // <?php echo esc_attr( $related_articles_heading_classes ?? '' ); ?>">
			<?php if ( ! empty( $c_icon ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $c_icon, true ); ?>
			<?php } ?>

			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
		</div>

		<div class="related_articles__wrap // lrv-a-scrollable-grid@desktop-max <?php echo esc_attr( $related_articles_wrap_classes ?? '' ); ?>">
			<div class="related_articless__list // <?php echo esc_attr( $related_articles_list_classes ?? '' ); ?>">
				<?php foreach ( $related_articles ?? [] as $item ) { ?>
					<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/related-article.php', $item, true ); ?>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
