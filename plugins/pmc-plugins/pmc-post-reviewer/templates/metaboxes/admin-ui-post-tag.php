<?php
/**
 * Template for the admin UI tags metabox
 *
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */

\PMC::render_template(
	sprintf( '%s/templates/metaboxes/admin-ui-term.php', PMC_POST_REVIEWER_ROOT ),
	[
		'admin_ui'   => $admin_ui,
		'post'       => $post,
		'pagehook'   => $pagehook,
		'term_type'  => 'post_tag',
		'term_label' => 'tag',
	],
	true
);


//EOF
