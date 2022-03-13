<?php
/**
 * Metabox UI template
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */
?>
<div id="mb-<?php echo esc_attr( $metabox['id'] ); ?>" class="<?php echo esc_attr( $metabox['class'] ); ?>">
	<?php call_user_func_array( $callback, $callback_args ); ?>
</div>
