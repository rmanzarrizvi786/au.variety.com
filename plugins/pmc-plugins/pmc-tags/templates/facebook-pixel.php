<?php
$option['values']['id'] = ( ! empty ( $option['values']['id'] ) ) ? $option['values']['id'] : '';

$blocker_atts = [
	'type'  => 'text/javascript',
	'class' => '',
];

if ( class_exists( '\PMC\Onetrust\Onetrust', false ) ) {
	$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0004' );
}
?>
<!-- Facebook Pixel Code -->
<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', <?php echo wp_json_encode( $option['values']['id'] ); ?>);
fbq('track', 'PageView');
</script>
<noscript><img alt="" height="1" width="1" style="display:none" src="<?php printf( 'https://www.facebook.com/tr?id=%s&ev=PageView&noscript=1', esc_attr( $option['values']['id'] ) ); ?>" /></noscript>
<!-- End Facebook Pixel Code -->
