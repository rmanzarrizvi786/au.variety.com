<?php
/**
 * Localize Video Player Events.
 */

if ( empty( $pmc_video_player ) || empty( $events ) ) {
	return;
}
?>
<!-- Start: Localize Video Player Events -->
<script type="text/javascript">
/* <![CDATA[ */
	var <?php echo esc_html( $pmc_video_player ); ?> = <?php echo wp_json_encode( $events ); ?>;
/* ]]> */
</script>
<!-- End: Localize Video Player Events -->
