<?php
$option['values']['id'] = ( ! empty ( $option['values']['id'] ) ) ? $option['values']['id'] : '';

// sanitize the $options['values']['id'].
$sanitized_id = intval( $option['values']['id'] );

$blocker_atts = [
	'type'  => 'text/javascript',
	'class' => '',
];
 
if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
    $blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0004' ); //pass the category of the script belongs
}

$args = [
	'notify' => 'event',
	'name'   => 'page_view',
	'id'     => $sanitized_id ?: '',
];
?>

<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">

window._tfa = window._tfa || [];
window._tfa.push(<?php echo wp_json_encode( $args ); ?>);
!function (t, f, a, x) {
if (!document.getElementById(x)) {
t.async = 1;t.src = a;t.id=x;f.parentNode.insertBefore(t, f);
}
}(document.createElement('script'),
document.getElementsByTagName('script')[0],
'<?php echo esc_url_raw( sprintf( '//cdn.taboola.com/libtrc/unip/%s/tfa.js', $sanitized_id ) ); ?>',
'tb_tfa_script');
</script>
<noscript>
<img src="<?php echo esc_url_raw( sprintf( '//trc.taboola.com/%s/log/3/unip?en=page_view', $sanitized_id ) ); ?>" width="0" height="0" style="display:none" alt=""/>
</noscript>
