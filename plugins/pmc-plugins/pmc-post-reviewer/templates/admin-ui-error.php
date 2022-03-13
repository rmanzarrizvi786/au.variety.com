<?php
/**
 * Template for the admin page errors
 *
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */

if ( empty( $error ) ) {
	return;
}

?>
<div class="wrap">

	<h2><?php echo esc_html__( 'Post Reviewer', 'pmc-post-reviewer' ); ?></h2>

	<div class="error">
		<p><?php echo esc_html( $error ); ?></p>
	</div>

</div>

