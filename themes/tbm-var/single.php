<?php

/**
 * Single Post Template.
 *
 * @package pmc-variety
 */

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
