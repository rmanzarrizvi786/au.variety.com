<?php
/**
 * Comments Template.
 *
 * Copied from pmc-variety-2017. For writing CSS, there is a static
 * representation of this markup in the Larva server in
 * src/patterns/one-offs/DEV-comments.
 *
 *
 * @package pmc-variety-2020
 */

if ( post_password_required() ) {
	return;
}

global $wp_query;

$comment_count = $wp_query->comment_count;

$title_reply   = __( 'No Comments', 'pmc-variety' );

if ( $comment_count > 1 ) {

	/* translators: %d comments count. */
	$title_reply = sprintf( _n( '%d Comment', '%d Comments', $comment_count, 'pmc-variety' ), $comment_count );
}

$leave_reply_header_class = ( ! comments_open() ) ? 'hidden' : '';

?>

<?php // Note: the markup in this element is replaced with DOM from JS ?>
<div id="article-comments" data-nosnippet >

	<?php
	if ( comments_open() ) {
		comment_form(
			array(
				'class_form'           => 'lrv-u-font-family-secondary',
				'comment_notes_before' => '',
				'comment_field' => sprintf(
					'<p class="comment-form-comment">%s %s %s</p>',
					sprintf(
						'<span
							for="number-of-comments"
							class="number-of-comments c-link u-font-size-16 u-font-size-14@tablet u-letter-spacing-012 u-color-pale-sky-2 u-color-black:hover o-comments-link--icon lrv-a-icon-before a-icon-comments-black lrv-u-text-transform-uppercase lrv-u-display-flex lrv-u-align-items-center lrv-u-justify-content-center lrv-u-line-height-large lrv-u-padding-a-050 lrv-u-width-100p u-font-family-basic a-content-ignore"
						>%s</span>',
						/* translators: %d comments count. */
						sprintf( _n( '%d Comment', '%d Comments', $comment_count, 'pmc-variety' ), $comment_count )
					),
					sprintf(
						'<label
							for="comment"
							class="lrv-u-display-block lrv-u-text-transform-uppercase lrv-u-font-size-14 lrv-u-font-weight-bold"
						>%s</label>',
						__( 'Leave a Reply', 'pmc-variety' )
					),
					sprintf(
						'<textarea
							id="comment"
							name="comment"
							cols="45"
							rows="8"
							maxlength="1500"
							required="required"
							class="lrv-u-width-100p lrv-u-font-size-12 u-font-family-basic"
							placeholder="%s"
						></textarea>',
						__( 'Enter your comment here', 'pmc-variety' )
					)
				),
			)
		);
	}
	?>

	<?php if ( have_comments() ) { ?>

		<div style="clear: both"></div>

		<div id="comments">

			<?php
			$comments_nav_args = array(
				'prev_text' => __( 'Older comments &rarr;', 'pmc-variety' ),
				'next_text' => __( '&larr; Newer comments', 'pmc-variety' ),
			);

			the_comments_navigation( $comments_nav_args );
			?>

		<?php if ( comments_open() ) { ?>
			<ol id="comment-list-wrapper" class="commentlist commentlist-partial-hidden">
				<?php
				// Display the list of comments.
				wp_list_comments(
					array(
						'reverse_top_level' => false,
						'avatar_size'       => 40,
					)
				);
				?>
			</ol>
		<?php } ?>
			<?php the_comments_navigation( $comments_nav_args ); ?>

		</div>

	<?php } ?>

</div>
