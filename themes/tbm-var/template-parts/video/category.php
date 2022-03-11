<?php
/**
 * Video Playlist Archive Template.
 *
 * @package pmc-variety
 */

\PMC::render_template(
	sprintf( '%s/template-parts/video/video-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

if ( is_tax( 'vcategory', 'contenders' ) ) {
	\PMC::render_template(
		sprintf( '%s/template-parts/video/popular-videos.php', untrailingslashit( CHILD_THEME_PATH ) ),
		[
			'channel' => 'active_contenders_channels',
		],
		true
	);
} else {
	\PMC::render_template(
		sprintf( '%s/template-parts/video/grid.php', untrailingslashit( CHILD_THEME_PATH ) ),
		[],
		true
	);
}

\PMC::render_template(
	sprintf( '%s/template-parts/video/explore-playlists.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);
