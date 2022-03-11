<?php

/**
 * Comments Button
 *
 * This module displays below the article content and provides a
 * means of skipping the ads below an article to go to comments.
 *
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/comments-button.prototype' );

$data['o_comments_button']['c_link']['c_link_text'] = sprintf(
	'%s',
	__( 'Comments', 'pmc-variety' )
);

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/comments-button.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
