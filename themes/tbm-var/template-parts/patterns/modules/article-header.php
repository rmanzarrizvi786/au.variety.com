<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="article-header // <?php echo esc_attr( $article_header_classes ?? '' ); ?>">
	<div class="article-header__inner // <?php echo esc_attr( $article_header_inner_classes ?? '' ); ?>">
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/article-meta.php', $article_meta, true ); ?>

		<?php if ( ! empty( $c_sponsored ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/components/c-sponsored.php', $c_sponsored, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_taxonomy_highlight ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/components/c-taxonomy-highlight.php', $c_taxonomy_highlight, true ); ?>
		<?php } ?>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-title.php', $o_title, true ); ?>

		<?php if ( ! empty( $o_custom_paragraph ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-custom-paragraph.php', $o_custom_paragraph, true ); ?>
		<?php } ?>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/author-social.php', $author_social, true ); ?>
	</div>

	<?php if ( ! empty( $o_figure ) ) { ?>
		<div class="article-header__feature // <?php echo esc_attr( $article_header_feature_classes ?? '' ); ?>">
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-figure.php', $o_figure, true ); ?>
		</div>
	<?php } ?>

	<?php if ( ! empty( $dirt_details ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/dirt-details.php', $dirt_details, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $linked_gallery ) ) { ?>
		<div class="article-header__feature // <?php echo esc_attr( $article_header_feature_classes ?? '' ); ?>">
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/linked-gallery.php', $linked_gallery, true ); ?>
		</div>
	<?php } ?>

	<?php if ( ! empty( $featured_video ) ) { ?>
		<div class="article-header__feature // <?php echo esc_attr( $article_header_feature_classes ?? '' ); ?>">
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/featured-video.php', $featured_video, true ); ?>
		</div>
	<?php } ?>
</div>
