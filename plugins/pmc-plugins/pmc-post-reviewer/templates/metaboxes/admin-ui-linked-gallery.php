<?php
/**
 * Template for the admin UI Linked Gallery metabox
 *
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */


$errors = [
	'not-found' => __( 'No linked gallery found', 'pmc-post-reviewer' ),
];

if ( ! class_exists( '\PMC_Gallery_Defaults' ) ) {

	echo esc_html( $errors['not-found'] );

} else {

	$linked_data = get_post_meta( $post->ID, \PMC_Gallery_Defaults::name . '-linked-gallery', true );

	if ( ! is_array( $linked_data ) || empty( $linked_data[0] ) || empty( $linked_data[2] ) ) {

		echo esc_html( $errors['not-found'] );

	} else {

		?>
		<strong><?php echo esc_html__( 'Linked Gallery', 'pmc-post-reviewer' ); ?>: </strong>
		<a href="<?php echo esc_url( $linked_data[0] ); ?>"><?php echo esc_html( $linked_data[2] ); ?></a>
		<?php

	}

}


//EOF
