<?php
/**
 * Template for [pmc-ndn] shortcode
 *
 * @since 2016-12-05 Amit Gupta
 */
?>
<script type="text/javascript">
if ( typeof window.ndn_script_is_loaded === "undefined" && pmc !== undefined ) {
	window.ndn_script_is_loaded = true;
	pmc.load_script( '//launch.newsinc.com/js/embed.js', '', '_nw2e-js' );
}
</script>

<?php if ( ! empty( $placementid ) ) { ?>

<div
	class="<?php echo esc_attr( $class ); ?>"
	data-config-playlist-id="<?php echo esc_attr( $playlistid ); ?>"
	data-config-distributor-id="<?php echo esc_attr( $trackinggroup ); ?>"
	id="<?php echo esc_attr( $placementid ); ?>"
	style="width:100%;"
	data-config-height="9/16w"></div>

<?php } else { ?>

<div
	class="<?php echo esc_attr( $class ); ?>"
	data-config-playlist-id="<?php echo esc_attr( $playlistid ); ?>"
	data-config-site-section="<?php echo esc_attr( $sitesection ); ?>"
	data-config-playlist-id="<?php echo esc_attr( $playlistid ); ?>"
	data-config-widget-id="2"
	data-config-type="VideoPlayer/Single"
	style="width:<?php echo floatval( $width ); ?>px;height:<?php echo floatval( $height ); ?>px;"
	data-config-tracking-group="<?php echo esc_attr( $trackinggroup ); ?>"
	data-config-video-id="<?php echo esc_attr( $videoid ); ?>"></div>


<?php
}

//EOF
