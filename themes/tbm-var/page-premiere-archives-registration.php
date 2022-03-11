<?php
/*
 * Template Name: Variety Archives
 */

get_header();

$page = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/page-variety-archives.prototype' );

?>

<article id="page" class="lrv-a-wrapper lrv-u-margin-tb-1">

	<?php
	$page['article_title']['article_title_markup'] = get_the_title();

	$page['c_lazy_image']['c_lazy_image_link_url']        = false;
	$page['c_lazy_image']['c_lazy_image_alt_attr']        = __( 'Historical images of Variety covers', 'pmc-variety' );
	$page['c_lazy_image']['c_lazy_image_src_url']         = CHILD_THEME_URL . '/assets/build/images/variety-over-the-years.jpg';
	$page['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();

	\PMC::render_template(
		sprintf( '%s/template-parts/patterns/modules/page-variety-archives.php', untrailingslashit( CHILD_THEME_PATH ) ),
		$page,
		true
	);
	?>

</article>

<?php

get_footer();
