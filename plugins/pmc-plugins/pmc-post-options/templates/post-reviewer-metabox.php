<?php
/**
 * Template for the admin UI Post Options metabox in Post Reviewer screen
 *
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */


if ( ! class_exists( '\PMC\Post_Options\Taxonomy', false ) ) {

	echo 'No post option assigned to this post';

	return;

}

$terms = get_the_terms( $post->ID, \PMC\Post_Options\Taxonomy::NAME );

if ( empty( $terms ) || is_wp_error( $terms ) || ! is_array( $terms ) ) {
	echo 'No post option assigned to this post';
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
