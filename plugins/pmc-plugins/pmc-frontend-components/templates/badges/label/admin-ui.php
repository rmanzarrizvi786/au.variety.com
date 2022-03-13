<?php
/**
 * Template to render admin UI data for the Badge Label
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2021-07-01
 */

if ( empty( $data ) || ! is_array( $data ) ) {
	return;
}

?>
<script>
	var pmcfcBadgeLabelData = <?php echo wp_json_encode( $data ); ?>;
</script>
