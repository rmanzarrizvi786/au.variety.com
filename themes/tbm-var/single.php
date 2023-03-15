<?php

/**
 * Single Post Template.
 *
 * @package pmc-variety
 */

 if (post_password_required($post)) {
	if (is_user_logged_in()) {
		do_action( 'wp_footer','wp_admin_bar_render' );
	}

	get_template_part('template-parts/protected/header');
		echo get_the_password_form();
	get_template_part('template-parts/protected/footer');

	return '';
}

get_header();

$single_template = \Variety\Inc\Featured_Article::get_instance()->is_featured_article() ? 'single-featured' : 'single';

?>
<div id="single-wrap">
	<?php
	\PMC::render_template(
		sprintf('%s/template-parts/article/' . $single_template . '.php', untrailingslashit(CHILD_THEME_PATH)),
		[],
		true
	);
	?>
</div>
<?php
get_footer();
