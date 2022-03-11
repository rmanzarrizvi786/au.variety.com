<?php
/**
 * Template for AMP excerpt.
 *
 * @package pmc-variety
 */
$sub_heading = get_post_meta( get_the_ID(), '_variety-sub-heading', true );
if ( empty( $sub_heading ) ) {
	return;
}
?>

<div class="amp-wp-excerpt">
	<?php echo wp_kses_post( $sub_heading ); ?>
</div>
