<?php
/**
 * Comments Template.
 *
 * @package pmc-variety
 */

if ( ! \PMC\Gallery\View::is_vertical_gallery() ) {
	if ( class_exists( '\PMC\Yappa\Yappa' ) ) {
		?>
		<div id="article-comments" data-nosnippet>
			<?php do_action( 'variety_render_comments' ); ?>
		</div>
		<?php
	}

	comments_template( sprintf( '%s/template-parts/comments/comments.php', untrailingslashit( CHILD_THEME_PATH ) ) );
}
