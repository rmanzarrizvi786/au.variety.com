<?php
/**
 * Template to output markup for Sponsored Content Badge
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-07-19
 */

if ( empty( $badge_label ) ) {
	return;
}
?>
<div class="pmc-frontend-components badges-sponsored-content">
	<?php echo esc_html( $badge_label ); ?>
</div>
