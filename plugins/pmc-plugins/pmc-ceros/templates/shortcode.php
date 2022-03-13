<?php
if ( empty( $src ) ) {
	return;
}
?>
<div style='<?php echo esc_attr( $div_style ); ?>' id='<?php echo esc_attr( $id ); ?>' data-aspectRatio='<?php echo esc_attr( $aspect_ratio ); ?>' <?php if ( ! empty( $mobile_aspect_ratio ) ) { echo "data-mobile-aspectRatio='" . esc_attr( $mobile_aspect_ratio ) . "'"; } ?>><iframe allowfullscreen src='<?php echo esc_url( $src ); ?>' style='<?php echo esc_attr( $iframe_style ); ?>' frameborder='0' class='ceros-experience' scrolling='no'></iframe></div><script type='text/javascript' src='//view.ceros.com/scroll-proxy.min.js' data-ceros-origin-domains='view.ceros.com'></script>
