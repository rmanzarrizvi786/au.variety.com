<?php if ( ! PMC::is_production() ) : return; endif; ?>
<?php
// Ignoring coverage temporarily 
// @codeCoverageIgnoreStart
$blocker_atts = [
	'type'  => 'text/javascript',
	'class' => '',
];

if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
	$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0002' );
}
// @codeCoverageIgnoreEnd
?>

<!-- Hotjar Tracking Code for <?php echo esc_url( site_url() ); ?> -->
<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">
	(function(h,o,t,j,a,r){
		h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
		h._hjSettings={hjid:<?php echo esc_js( $option['values']['id'] ); ?>,hjsv:5};
		a=o.getElementsByTagName('head')[0];
		r=o.createElement('script');r.async=1;
		r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
		a.appendChild(r);
	})(window,document,'//static.hotjar.com/c/hotjar-','.js?sv=');
</script>
