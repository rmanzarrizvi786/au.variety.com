<?php
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

// PASE-735: Digioh checks for a UTM parameter containing `6032972` to indicate the visitor came from an email.
$query_string = wp_parse_url(
	PMC::filter_input(
		INPUT_SERVER,
		'REQUEST_URI'
	),
	PHP_URL_QUERY
);
$visitor_from_email = false !== strpos( $query_string, '6032972' );

?>

<!--START Lightbox Javascript-->
<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">
	var scrollSubscriber = document.cookie.indexOf("scroll0=") > -1;

	if ( ! scrollSubscriber ) {
		var digiohVisitorFromEmail = !! <?php echo $visitor_from_email ? 1 : 0; ?>;

		(function(){
			window.setTimeout(
				function() {
					var tag = document.createElement( 'script' );
					tag.type = "text/javascript";
					tag.src   = "<?php echo esc_url( sprintf( 'https://www.lightboxcdn.com/vendor/%s/lightbox_inline.js', $option['values']['id'] ) ); ?>";
					tag.async = true;

					var parent = document.getElementsByTagName( 'script' )[0];
					parent.parentNode.insertBefore( tag, parent );
				},
				500
			);
		})();
	}
</script>
<!--END Lightbox Javascript-->

<?php
unset( $query_string, $visitor_from_email );
