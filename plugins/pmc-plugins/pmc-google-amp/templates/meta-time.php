<?php
/**
 * Template for publish timestamp.
 * Copied from AMP plugin from MU plugins.
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2017-11-14
 *
 * @package pmc-google-amp
 */

?>
<div class="amp-wp-meta amp-wp-posted-on">
	<time datetime="<?php echo esc_attr( get_post_time( 'c', true ) ); ?>">
		<?php echo esc_html( get_the_time( 'F j, Y g:iA T' ) ); ?>
	</time>
</div>