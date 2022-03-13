<?php
$is_sticky_ad = ( 'yes' === $amp_sticky_ad_status && 'amp-adhesion' === $ad_slot );

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
				width="<?php echo intval( $width ); ?>"
				height="<?php echo intval( $height ); ?>"
				type="shemedia"
				data-slot-type="<?php echo esc_attr( $slot_type ); ?>"
				data-boomerang-path="<?php echo esc_attr( $boomerang_path ); ?>"
				json='<?php echo esc_attr( wp_json_encode( $json ) ); ?>'>
			<div fallback></div>
		</amp-ad>

	<?php if ( true === $is_sticky_ad ) { ?>
		</amp-sticky-ad>
	<?php } ?>

</div>
