<?php
$is_sticky_ad = ( 'yes' === $amp_sticky_ad_status && 'amp-adhesion' === $ad_slot );
$multi_size_attr = '';

if ( ! empty( $multi_size ) ) {
	$multi_size_attr = sprintf( 'data-multi-size=%s layout=fixed', $multi_size );
}

if ( class_exists( '\PMC\Partner_Scroll\Plugin' ) && \PMC\Partner_Scroll\Plugin::get_instance()->is_scroll_enabled() ) {
    ?>
        <div class="ad-slot <?php echo esc_attr( $css_class ); ?>" amp-access="NOT scroll.scroll">
    <?php
} else {
    ?>
        <div class="ad-slot <?php echo esc_attr( $css_class ); ?>">
    <?php
}

	if ( true === $is_sticky_ad ) { ?>
		<amp-sticky-ad layout="nodisplay">
	<?php } ?>

		<amp-ad
				id="<?php echo esc_attr( $ad_div_id ); ?>"
				width="<?php echo intval( $width ); ?>"
				height="<?php echo intval( $height ); ?>"
				data-enable-refresh="<?php echo intval( $refresh_interval ); ?>"
				type="doubleclick"
				rtc-config='<?php echo esc_attr( wp_json_encode( $rtc_config ) ); ?>'
				json='<?php echo esc_attr( wp_json_encode( $json ) ); ?>'
				data-slot="<?php echo esc_attr( $slot ); ?>"
				<?php echo esc_attr( $multi_size_attr ); ?>>
			<div fallback></div>
		</amp-ad>

	<?php if ( true === $is_sticky_ad ) { ?>
		</amp-sticky-ad>
	<?php } ?>

</div>
