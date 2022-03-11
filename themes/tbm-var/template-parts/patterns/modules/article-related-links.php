<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="article-related-links // a-pull-3@tablet lrv-u-text-align-center@tablet u-width-250@tablet lrv-u-padding-lr-050 lrv-a-floated-left@tablet lrv-u-margin-r-1 lrv-u-margin-b-1">
	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>

	<div class="<?php echo esc_attr( $article_related_item_classes ?? '' ); ?> u-border-a-10@tablet u-padding-lr-1@tablet u-padding-b-1@tablet">
		<?php if ( ! empty( $c_lazy_image ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true ); ?>
		<?php } ?>

		<div class="a-children-border--grey-light a-children-border-vertical lrv-u-font-family-primary">
			<?php foreach ( $article_related_links ?? [] as $item ) { ?>
				<h3 itemprop="headline" class="<?php echo esc_attr( $article_related_links_classes ?? '' ); ?> lrv-u-font-size-16 lrv-u-line-height-small lrv-u-font-size-18@tablet lrv-u-font-weight-normal lrv-u-padding-tb-050">
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $item, true ); ?>
				</h3>
			<?php } ?>
		</div>
	</div>
</section>
