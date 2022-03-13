<?php
/**
 * Template for the admin UI featured image metabox
 *
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */

if ( ! has_post_thumbnail( $post->ID ) ) {
	echo esc_html__( 'No featured image attached to this post', 'pmc-post-reviewer' );
} else {

	$image_id       = get_post_thumbnail_id( $post->ID );
	$image_edit_url = get_edit_post_link( $image_id );
	$image_url      = wp_get_attachment_url( $image_id );
	$image_styles   = 'width: 100%; height: auto;';

	printf(
		'<a href="%1$s"><img src="%2$s" style="%3$s"><br>%4$s</a>',
		esc_url( $image_edit_url ),
		esc_url( $image_url ),
		esc_attr( $image_styles ),
		esc_html__( 'Click here to edit image', 'pmc-post-reviewer' )
	);

}


//EOF
