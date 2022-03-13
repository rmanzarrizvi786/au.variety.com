<?php
/**
 * Template for the admin UI featured video metabox
 *
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */

$error_msg = __( 'No featured video attached to this post', 'pmc-post-reviewer' );

if (
	! class_exists( 'PMC_Featured_Video_Override' )
	|| ! PMC_Featured_Video_Override::has_featured_video( $post->ID )
) {
	echo esc_html( $error_msg );
} else {

	$video = get_post_meta( $post->ID, PMC_Featured_Video_Override::META_KEY, true );

	if ( ! empty( $video ) ) {

		if ( is_numeric( $video ) ) {
			echo esc_html( $video );
		} else {
			printf(
				'<a href="%1$s" target="_blank">%1$s</a>',
				esc_url( $video )
			);
		}

	} else {
		echo esc_html( $error_msg );
	}

}


//EOF
