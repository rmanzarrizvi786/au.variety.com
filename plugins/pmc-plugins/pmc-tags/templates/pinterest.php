<?php
// moved from Mezzobit. SADE-226
if ( empty( $option['values']['id'] ) ) {
	return;
}

// Ignoring coverage temporarily 
// @codeCoverageIgnoreStart
$blocker_atts = [
	'type'  => 'text/javascript',
	'class' => '',
];

if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
	$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0004' );
}
// @codeCoverageIgnoreEnd
?>

<!-- Pinterest Tag -->
<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">
!function(e){if(!window.pintrk){window.pintrk = function ()

{ window.pintrk.queue.push(Array.prototype.slice.call(arguments))}
;var
n=window.pintrk;n.queue=[],n.version="3.0";var
t=document.createElement("script");t.async=!0,t.src=e;var
r=document.getElementsByTagName("script")[0];
r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");
pintrk('load', '<?php echo wp_json_encode( $option['values']['id'] ); ?>',

{em: '<user_email_address>'}
);
pintrk('page');
</script>
<noscript>
<img height="1" width="1" style="display:none;" alt=""
src="https://ct.pinterest.com/v3/?tid=<?php echo rawurlencode( $option['values']['id'] ); ?>&pd[em]=<hashed_email_address>&noscript=1" />
</noscript>

<script>
pintrk('track', 'pagevisit');
</script>
<!-- end Pinterest Tag -->

