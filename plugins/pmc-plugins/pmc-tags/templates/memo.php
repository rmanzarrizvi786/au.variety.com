<?php
if ( empty( $option['values']['id'] ) ) {
	return;
}

$memo_config = [
	'pid' => $option['values']['id'],
];

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

<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">
__memo_config = <?php echo wp_json_encode( $memo_config ); ?>;
(function() {
	var s = document.createElement('script'); s.async = true; s.type = 'text/javascript'; s.src = document.location.protocol + '//cdn.memo.co/js/memo.js'; (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body') [0]).appendChild(s);
})();
</script>
