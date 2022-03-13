<?php
/**
 * Single Profile
 */

get_header();

?>

<?php PMC::render_template( PROFILES_ROOT . '/template-parts/shared/profile-header.php', [], true ); ?>

<?php
while ( have_posts() ) :

	the_post();

	?>

	<div class="lrv-a-wrapper">
		<div>
			<?php PMC::render_template( PROFILES_ROOT . '/template-parts/profile/back-to-list.php', [], true ); ?>
		</div>

		<section class="a-profile-grid">
			<div class="a-profile-grid__blurb">
				<?php PMC::render_template( PROFILES_ROOT . '/template-parts/profile/profile-blurb.php', [], true ); ?>
			</div>

			<div class="a-profile-grid__body">
				<?php PMC::render_template( PROFILES_ROOT . '/template-parts/profile/profile-body.php', [], true ); ?>
			</div>

			<div class="a-profile-grid__ads">
			<?php
			if ( is_active_sidebar( 'pmc_profiles_right' ) ) {

				dynamic_sidebar( 'pmc_profiles_right' );

			}
			?>
			</div>

			<div class="a-profile-grid__stories">
				<?php PMC::render_template( PROFILES_ROOT . '/template-parts/profile/profile-gallery.php', [], true ); ?>
				<?php PMC::render_template( PROFILES_ROOT . '/template-parts/profile/profile-related-stories.php', [], true ); ?>
			</div>
		</section>
	</div>

<?php endwhile; ?>

<?php get_footer(); ?>
