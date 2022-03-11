<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $article_title_outer ) ) { ?>
	<div class="article-title__outer <?php echo esc_attr( $article_title_outer_classes ?? '' ); ?>">
<?php } ?>
	<h1 class="article-title // <?php echo esc_attr( $article_title_classes ?? '' ); ?>"><?php echo wp_kses_post( $article_title_markup ?? '' ); ?></h1>

	<?php if ( ! empty( $c_tagline ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true ); ?>
	<?php } ?>

<?php if ( ! empty( $article_title_outer ) ) { ?>
	</div>
<?php } ?>
