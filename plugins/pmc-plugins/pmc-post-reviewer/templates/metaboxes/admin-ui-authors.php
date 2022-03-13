<?php
/**
 * Template for the admin UI authors metabox
 *
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */

$authors = \PMC::get_post_authors( $post->ID, 'all' );

if ( empty( $authors ) || ! is_array( $authors ) ) {
	echo esc_html__( 'No author found for this post', 'pmc-post-reviewer' );
} else {
	?>
	<ul>

		<?php
		foreach ( $authors as $author ) {

			$display_txt = '';

			if ( ! empty( $author['display_name'] ) ) {
				$display_txt = $author['display_name'];
			} elseif ( ! empty( $author['user_nicename'] ) ) {
				$display_txt = $author['user_nicename'];
			} elseif ( ! empty( $author['user_login'] ) ) {
				$display_txt = $author['user_login'];
			}

			if ( ! empty( $display_txt ) ) {
				printf( '<li>%s</li>', esc_html( $display_txt ) );
			}

			unset( $display_txt );

		}
		?>

	</ul>
	<?php
}


//EOF
