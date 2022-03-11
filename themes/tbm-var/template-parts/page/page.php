<?php

$variant = \Variety\Plugins\Variety_VIP\Content::is_vip_page() ? '.variety-vip' : '';

$article_title = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/article-title.page' . $variant );

$article_title['article_title_markup'] = get_the_title();

$conditional_classes = ! is_page( 'results' ) ? 'u-max-width-618 lrv-u-margin-lr-auto a-content' : '';

?>

<article id="page" class="lrv-a-wrapper lrv-u-margin-tb-1">

	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<?php
			\PMC::render_template(
				sprintf( '%s/template-parts/patterns/modules/article-title.php', untrailingslashit( CHILD_THEME_PATH ) ),
				$article_title,
				true
			);
			?>
			<div class="<?php echo esc_attr( $conditional_classes ); ?> u-font-family-body lrv-u-line-height-normal lrv-u-font-size-18">
				<?php the_content(); ?>
			</div>
			<?php

		endwhile;
	endif;
	?>

</article>
