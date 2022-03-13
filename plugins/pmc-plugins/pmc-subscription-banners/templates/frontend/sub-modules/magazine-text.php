<?php

$title         = sprintf( '<h2><strong>%s</strong></h2>', __( 'Get the Magazine', 'pmc-subscription-banners' ) );
$body_copy     = sprintf( '<h4>%s</h4>', __( 'Unleash the power to dream! Subscribe today and bring those dreams to life with every issue!', 'pmc-subscription-banners' ) );
$body_sub_copy = sprintf( '<h4>%s</h4>', __( 'Subscribe today and save up to 66%. Includes FREE digital access!', 'pmc-subscription-banners' ) );

if ( ! empty( $module_details ) ) {
	$title         = ( ! empty( $module_details['title'] ) ) ? $module_details['title'] : $title;
	$body_copy     = ( ! empty( $module_details['body_copy'] ) ) ? $module_details['body_copy'] : $body_copy;
	$body_sub_copy = ( ! empty( $module_details['body_sub_copy'] ) ) ? $module_details['body_sub_copy'] : $body_sub_copy;
}

?>
<div class="subscription-title"><?php echo wp_kses_post( $title ); ?></div>
<div class="subscription-body-copy"><?php echo wp_kses_post( $body_copy ); ?></div>
<div class="subscription-body-subcopy"><?php echo wp_kses_post( $body_sub_copy ); ?></div>

