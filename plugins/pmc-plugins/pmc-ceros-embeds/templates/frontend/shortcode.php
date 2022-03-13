<?php
/**
 * Shortcode template
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2020-08-12
 */

if (
	empty( $id ) || empty( $css ) || empty( $atts )
	|| empty( $atts['container_id'] )
	|| empty( $atts['container_style'] )
	|| empty( $atts['iframe_src'] )
) {
	return '';
}

?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $css ); ?>">
	<div
		id="<?php echo esc_attr( $atts['container_id'] ); ?>"
		style="<?php echo esc_attr( $atts['container_style'] ); ?>"
		data-aspectRatio="<?php echo esc_attr( $atts['container_aspect_ratio'] ); ?>"
		data-mobile-aspectRatio="<?php echo esc_attr( $atts['container_mobile_aspect_ratio'] ); ?>"
	>
		<iframe
			allowfullscreen
			src="<?php echo esc_url( $atts['iframe_src'] ); ?>"
			style="<?php echo esc_attr( $atts['iframe_style'] ); ?>"
			frameborder="0"
			class="<?php echo esc_attr( $atts['iframe_css'] ); ?>"
			title="<?php echo esc_attr( $atts['iframe_title'] ); ?>"
			scrolling="no"
		></iframe>
	</div>
	<?php // phpcs:disable ?>
	<script type="text/javascript" src="https://view.ceros.com/scroll-proxy.min.js" data-ceros-origin-domains="view.ceros.com"></script>
	<?php // phpcs:enable ?>
</div>
