<?php
/**
 * Template Name: Vscore Top 250
 *
 * @since 2017-08-16 Milind More CDWE-474
 *
 * @package pmc-variety-2017
 */

global $page_template;

$page_template = Variety_Vscore_Top::PAGE_TEMPLATE;

get_header();

if ( have_posts() ) {

	while ( have_posts() ) {

		the_post();
		?>
		<div class="l-page__content">
			<div class="l-wrap c-vscore-top">
				<div class="l-wrap__main">

					<div class="section">
						<h1 class="page_title">
							<a href="<?php echo esc_url( home_url( '/print-plus/', 'relative' ) ); ?>">
								<img src="<?php echo PMC::esc_url_ssl_friendly( get_stylesheet_directory_uri() . '/assets/build/images/premier/premier-logo.png' ) ?>" />
							</a>
							<span><?php the_title(); ?></span>
						</h1>
					</div> <!-- .section -->

				<?php
				PMC::render_template( CHILD_THEME_PATH . '/plugins/variety-vscore-top/templates/vscore-top-filters.php', [], true );
				?>
				</div> <!-- l-wrap__main -->
			</div><!-- .c-vscore-top .l-wrap-->
		</div><!-- .l-page__content -->
		<?php
	}
}

get_footer();
