<?php
if ( empty( $krux_config_id ) ) {
	return;
}

$blocker_atts = [
	'type'  => 'text/javascript',
	'class' => '',
];

if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
	$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'kxct optanon-category-C0004' );
}
?>

<!-- BEGIN Krux Control Tag -->
<script data-id="<?php echo esc_attr( $krux_config_id ); ?>" data-timing="async" data-version="3.0" type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">
  window.Krux||((Krux=function(){Krux.q.push(arguments)}).q=[]);

	Krux('ns:penskemediacorporation', 'consent:get', function(errors, body) {

		if ( 'undefined' !== typeof pmc_meta && 'undefined' !== typeof pmc_meta.country && 'us' === pmc_meta.country.toLowerCase() ) {
			Krux( 'ns:penskemediacorporation', 'consent:set', { dc: true, al: true, tg: true, cd: true, sh: true, re: true } );
		}
	} );

  (function(){
    var k=document.createElement('script');k.type='text/javascript';k.async=true;
    k.src=<?php echo wp_json_encode( esc_url( 'https://cdn.krxd.net/controltag/' . (string)$krux_config_id ) . '.js' ); ?>;
    var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(k,s);
  }());

</script>
<!-- END Krux Controltag -->

<!-- GPT interchange code -->
<script type="text/javascript">
	window.Krux || ((Krux = function() {
		Krux.q.push(arguments);
	}).q = []);
	(function() {
		function retrieve(n) {
			var m, k = 'kxpenskemediacorporation_' + n;
			if (window.localStorage) {
				return window.localStorage[k] || "";
			} else if (navigator.cookieEnabled) {
				m = document.cookie.match(k + '=([^;]*)');
				return (m && unescape(m[1])) || "";
			} else {
				return '';
			}
		}
		Krux.user = retrieve('user');
		Krux.segments = retrieve('segs') && retrieve('segs').split(',') || [];
	})();
</script>
