<?php
/*
 * Template to render [pmc_onescreen] shortcode UI
 *
 * @since 2014-12-17 Amit Gupta
 */
?>
<script type="text/javascript" src="//cdn.mediagraph.com/os/static/apps/2.0/_onescreen.js"></script>
<div id="<?php echo esc_attr( $target_div ); ?>"></div>
<script type="text/javascript">
	com.onescreen.apps.load( '<?php echo esc_js( $app_id ); ?>', '<?php echo esc_js( $target_div ); ?>', <?php echo json_encode( $config ); ?> );
</script>
