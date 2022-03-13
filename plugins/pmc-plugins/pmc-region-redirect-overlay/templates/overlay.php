<?php
/**
 * Overlay banner template
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2020-11-25
 */

if ( empty( $overlay_id ) ) {
	return;
}

?>
<div id="<?php echo esc_attr( $overlay_id ); ?>" class="<?php echo esc_attr( $overlay_id ); ?>">
	<a href="#" class="btn-close">&#x000D7;</a>
	<div class="message"></div>
</div>
