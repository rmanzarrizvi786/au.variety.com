<?php
/**
 * Template to output markup for Label Badge
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2021-06-29
 */

if ( empty( $label ) ) {
	return;
}
?>
<div class="pmc-frontend-components badges-label">
	<div class="badges-label--actual">
		<?php echo esc_html( $label ); ?>
	</div>
</div>
