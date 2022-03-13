<?php
if ( post_password_required() ) {
	return;
}

$comment_count     = get_comments_number( get_queried_object_id() );
$coment_count_text = $comment_count;

if ( 1 > $comment_count ) {
	$title_reply       = __( 'No Comments', 'tvline' );
	$coment_count_text = "";
} elseif ( 1 == $comment_count ) {
	$title_reply = __( " Comment", 'tvline' );
} else {
	$title_reply = __( " Comments", 'tvline' );
}
$visible = "";
if ( ! comments_open() ) {
	$visible = " style=visibility:hidden; ";
}

?>
<div id="pmc-lazy-comments-<?php echo get_the_ID(); ?>" class="module-comments">
	<?php

	if ( comments_open() ) {
		comment_form( array(
			'title_reply'          => '',
			'comment_notes_before' => '',
			'comment_notes_after'  => '',
			'id_form'              => "commentform-" . get_the_ID()
		) );
	}

	?>
	<div class="comment-count">
		<span><?php echo esc_html( $coment_count_text . $title_reply ); ?></span>

		<div class="comment-bubble"></div>
	</div>
	<?php

	if ( have_comments() ) :
		?>
		<div>
			<ol id="comment-list-wrapper" class="commentlist commentlist-partial-hidden">
			</ol>
			<div class='pre-next-comment-link'></div>
			<div class='next-comment-link-wrapper'>
				<?php
				$max_page = get_comment_pages_count();

				if ( 1 < $max_page ) { // generate See All comment with comments paging
					next_comments_link( __( 'See More ', 'pmc-ajax-comments' ) . $title_reply, $max_page );
				} else { // if we only have one page and more than one comments, we want to generate the See All comments link
					?>
					<a href="<?php echo esc_url( get_comments_pagenum_link( 1 ) ); ?>">
						<?php echo esc_html__( 'See More ', 'pmc-ajax-comments' ) . esc_html( $title_reply ); ?>
					</a>
					<?php
				}
				?>
			</div>
		</div>
	<?php endif;
	?>
</div>
