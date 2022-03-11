<?php \Variety\Plugins\Variety_500\Templates::header(); ?>

<?php \Variety\Plugins\Variety_500\Templates::site_header();
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
?>

<section class="l-search">
	<div class="l-search__intro">

		<div class="c-page-intro">
			<div class="c-page-intro__background-text">
				<?php the_title(); ?>
			</div>
			<h1 class="c-page-intro__text">
				<?php esc_html_e( 'Explore the Variety500', 'pmc-variety' ); ?>
			</h1>
		</div><!-- .c-page-intro -->

	</div><!-- .l-search__intro -->
	<div class="l-search__container">
		<?php the_content(); ?>
	</div><!-- .l-search__container -->
</section><!-- .l-search -->

<?php
	endwhile;
endif;
?>

<?php \Variety\Plugins\Variety_500\Templates::footer(); ?>
