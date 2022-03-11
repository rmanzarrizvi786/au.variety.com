<?php
/**
 * Template for AMP date and time.
 *
 * @package pmc-variety
 */

?>

<div class="amp-wp-meta amp-wp-posted-on">
	<time datetime="<?php echo esc_attr( get_post_time( 'c', true ) ); ?>">
		<?php echo esc_html( get_the_time( 'F j, Y g:ia' ) . ' PT' ); ?>
	</time>
</div>
