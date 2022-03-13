<?php

if ( class_exists( '\PMC\Partner_Scroll\Plugin' ) && \PMC\Partner_Scroll\Plugin::get_instance()->is_scroll_enabled() ) {
	?>
        <div class="pmc-outbrain-amp-widget <?php echo esc_attr( $css_class ); ?>" amp-access="NOT scroll.scroll">
	<?php
} else {
	?>
        <div class="pmc-outbrain-amp-widget <?php echo esc_attr( $css_class ); ?>">
	<?php
}
?>

    <amp-embed width="100" height="100"
		type="outbrain"
		layout="responsive"
		data-widgetIds="<?php echo esc_attr( $widget_ids ); ?>"
		data-htmlURL="<?php echo esc_url( $html_url ); ?>"
		data-ampURL="<?php echo esc_url( $amp_url ); ?>">
	</amp-embed>
</div>
<?php
//EOF
