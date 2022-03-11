<?php
/**
 * Template for Yappa Widget commenting system
 */
?>

<div
	id="yappa-comments-frame"
	data-title="<?php echo esc_attr( get_the_title( $current_post ) ); ?>"
	data-url="<?php echo esc_url( get_permalink( $current_post ) ); ?>"
	data-id="<?php echo esc_attr( $current_post->ID ); ?>">
</div>
