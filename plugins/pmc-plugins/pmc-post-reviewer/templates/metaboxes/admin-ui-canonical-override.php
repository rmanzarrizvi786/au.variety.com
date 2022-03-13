<?php
/**
 * Template for the admin UI Canonical Override metabox
 *
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */


$errors = [
	'not-found' => __( 'No Canonical Override found', 'pmc-post-reviewer' ),
];

$canonical_override_url = get_post_meta( $post->ID, '_pmc_canonical_override', true );


if ( empty( $canonical_override_url ) ) {

	echo esc_html( $errors['not-found'] );

} else {

	?>
	<p>
		<label for="pmc_canonical_override">
			<strong><?php echo esc_html__( 'URL', 'pmc-post-reviewer' ); ?>:</strong>
		</label>
		<br><br>
		<input type="text" class="wide-seo-box" name="pmc_canonical_override" id="pmc_canonical_override" value="<?php echo esc_attr( $canonical_override_url ); ?>">
	</p>
	<?php

}

