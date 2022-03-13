<?php
/**
 * Template for the admin UI term metaboxes
 *
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-01-16
 */

$terms = get_the_terms( $post->ID, $term_type );

if ( empty( $terms ) || is_wp_error( $terms ) || ! is_array( $terms ) ) {

	$err_msg = sprintf( 'No %s assigned to this post', $term_label );

	// ignoring below line in phpcs since it's flagging use of a var
	echo esc_html__( $err_msg, 'pmc-post-reviewer' );    // phpcs:ignore

} else {

	$term_count = count( $terms );

	?>
	<ul>

		<?php
		for ( $i = 0; $i < $term_count; $i++ ) {

			if ( empty( $terms[ $i ]->term_id ) || empty( $terms[ $i ]->name ) || empty( $terms[ $i ]->slug ) ) {
				continue;
			}

			printf(
				'<li>(%d) %s <strong>&rarr;</strong> %s</li>',
				intval( $terms[ $i ]->term_id ),
				esc_html( $terms[ $i ]->slug ),
				esc_html( $terms[ $i ]->name )
			);

		}
		?>

	</ul>
	<?php
}


//EOF
