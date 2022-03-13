<?php
$title     = sprintf( '<h2><strong>%s</strong></h2>', __( 'Get the Magazine', 'pmc-subscription-banners' ) );
$body_copy = sprintf( '<h4>%s</h4>', __( 'Subscribe today and save up to 66%. Includes FREE digital access!', 'pmc-subscription-banners' ) );

if ( ! empty( $module_details ) ) {
	$title     = ( ! empty( $module_details['title'] ) ) ? $module_details['title'] : $title;
	$body_copy = ( ! empty( $module_details['body_copy'] ) ) ? $module_details['body_copy'] : $body_copy;
}

?>
<div class="subscription-title"><?php echo wp_kses_post( $title ); ?></div>
<div class="subscription-body-copy"><?php echo wp_kses_post( $body_copy ); ?></div>
