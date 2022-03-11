<?php
/**
 * Single PMC Gallery Template.
 *
 * @package pmc-variety
 */

use PMC\Gallery\View;

if ( \PMC\Gallery\View::is_vertical_gallery() ) {
	\PMC::render_template(
		sprintf( '%s/single.php', untrailingslashit( CHILD_THEME_PATH ) ),
		[],
		true
	);
	return;
}

get_header();

// Standard Gallery.
View::get_instance()->render_gallery();

wp_footer();

do_action( 'pmc-tags-footer' ); // @codingStandardsIgnoreLine

do_action( 'pmc-tags-bottom' ); // @codingStandardsIgnoreLine

?>

</body>
</html>
