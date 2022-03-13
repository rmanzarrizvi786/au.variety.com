<?php
/**
 * Display the magazine subscribe button
 */

$url              = '';
$button_copy      = sprintf( '<span>%s</span>', __( 'Subscribe', 'pmc-subscription-banners' ) );
$gift_copy        = sprintf( '<span>%s</span>', __( 'Give the Gift of Luxury', 'pmc-subscription-banners' ) );
$gift_url         = '';
$class            = 'subscription-links-link';
$background_color = '#000000';

if ( ! empty( $module_details ) ) {
	$url              = ( ! empty( $module_details['subscribe_link'] ) ) ? $module_details['subscribe_link'] : $url;
	$button_copy      = ( ! empty( $module_details['subscribe_copy'] ) ) ? $module_details['subscribe_copy'] : $button_copy;
	$gift_copy        = ( ! empty( $module_details['gift_copy'] ) ) ? $module_details['gift_copy'] : $gift_copy;
	$gift_url         = ( ! empty( $module_details['gift_link'] ) ) ? $module_details['gift_link'] : $gift_url;
	$class            = ( ! empty( $module_details['gift_link'] ) ) ? $class : $class . ' hide';
	$background_color = ( ! empty( $module_details['subscribe_background_color'] ) ) ? $module_details['subscribe_background_color'] : $background_color;
}


?>
<div class="subscription-links">
	<a href="<?php echo esc_url( $url ); ?>" class="button subscription-links-button" style="background-color:<?php echo esc_attr( $background_color ); ?>"><?php echo wp_kses_post( $button_copy ); ?></a>
	<a href="<?php echo esc_url( $gift_url ); ?>" class="<?php echo esc_attr( $class ); ?>"><?php echo wp_kses_post( $gift_copy ); ?></a>
</div>
