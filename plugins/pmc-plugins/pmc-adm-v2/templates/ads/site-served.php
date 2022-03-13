<?php

if ( empty( $ad['ad-url'] ) || empty( $ad['ad-image'] ) || empty( $ad['width'] ) || empty( $ad['height'] ) ) {
	return;
}

$url           = $ad['ad-url'];
$image_url     = $ad['ad-image'];
$width         = $ad['width'];
$height        = $ad['height'];
$ad_class      = ( ! empty( $ad['css-class'] ) ) ? $ad['css-class'] : '';
$title         = ( ! empty( $ad['title'] ) ) ? $ad['title'] : '';
$promo         = ( ! empty( $ad['ad-promo'] ) ) ? $ad['ad-promo'] : '';
$creative      = ( ! empty( $ad['ad-creative'] ) ) ? $ad['ad-creative'] : '';
$campaign_name = ( ! empty( $ad['ad-campaign-name'] ) ) ? $ad['ad-campaign-name'] : '';
$location      = ( ! empty( $ad['location'] ) ) ? $ad['location'] : '';

$parsed_url = wp_parse_url( $url );
if ( false === $parsed_url || empty( $parsed_url['scheme'] ) ) {
	$url = home_url( $url );
}

?>
<div class="pmc-adm-site-served <?php echo esc_attr( $ad_class ); ?>">
	<a class="pmc-adm-site-served-link <?php echo esc_attr( 'pmc-adm-site-served-' . $location ); ?>"
		href="<?php echo esc_url( $url ); ?>"
		title="<?php echo esc_attr( $title ); ?>"
		data-promo="<?php echo esc_attr( $promo ); ?>"
		data-creative="<?php echo esc_attr( $creative ); ?>"
		data-campaign-name="<?php echo esc_attr( $campaign_name ); ?>"
		data-position="<?php echo esc_attr( $location ); ?>">
		<img class="pmc-adm-site-served-ad-image"
			src="<?php echo esc_url( $image_url ); ?>"
			width="<?php echo esc_attr( $width ); ?>"
			height="<?php echo esc_attr( $height ); ?>"/>
	</a>
</div>

