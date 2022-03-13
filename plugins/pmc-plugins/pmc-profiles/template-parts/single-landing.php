<?php
/**
 * Single landing page
 */

get_header();

// This will be in one-off block.
PMC::render_template( PROFILES_ROOT . '/template-parts/shared/profile-header.php', [], true );

while ( have_posts() ) :

	the_post();

	the_content();

endwhile;

get_footer();

