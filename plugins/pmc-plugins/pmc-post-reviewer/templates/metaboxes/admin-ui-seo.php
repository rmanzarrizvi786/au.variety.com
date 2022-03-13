<?php
/**
 * Template for the admin UI SEO metabox
 *
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */


$errors = [
	'not-found' => __( 'No SEO data found', 'pmc-post-reviewer' ),
];

$seo_title       = get_post_meta( $post->ID, 'mt_seo_title', true );
$seo_description = get_post_meta( $post->ID, 'mt_seo_description', true );


if ( empty( $seo_title ) && empty( $seo_description ) ) {

	echo esc_html( $errors['not-found'] );

} else {

	?>
	<p>
		<label for="mt_seo_title"><strong><?php echo esc_html__( 'Title', 'pmc-post-reviewer' ); ?>:</strong></label><br><br>
		<input type="text" class="wide-seo-box" name="mt_seo_title" id="mt_seo_title" value="<?php echo esc_attr( $seo_title ); ?>">
	</p>
	<p>&nbsp;</p>
	<p>
		<label for="mt_seo_description"><strong><?php echo esc_html__( 'Description', 'pmc-post-reviewer' ); ?>:</strong></label><br><br>
		<textarea class="wide-seo-box" rows="4" cols="40" name="mt_seo_description" id="mt_seo_description"><?php echo esc_attr( $seo_description ); ?></textarea>
	</p>
	<?php

}


//EOF
