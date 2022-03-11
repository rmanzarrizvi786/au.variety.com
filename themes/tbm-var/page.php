<?php

get_header();

\PMC::render_template(
	sprintf( '%s/template-parts/page/page.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer();
