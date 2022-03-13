<?php
/**
 * View file for rendering admin notices
 */
?>
<div class="<?php echo esc_attr( $type ); ?>"><p><?php echo wp_kses_post( $message ); ?></p></div>
