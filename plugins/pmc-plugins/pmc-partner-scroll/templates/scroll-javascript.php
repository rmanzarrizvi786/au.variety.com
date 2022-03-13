<?php
/**
 * Scroll's javascript tag. Responsible for identifying scroll users.
 * phpcs:ignorefile -- Ignoring the file because there's not a lot of logic and it's mainly plain javascript.
 */
$blocker_atts = [
	'type'  => 'text/javascript',
	'class' => '',
];

if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
	$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0003' );
}

?>

<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>" async src="https://static.scroll.com/js/scroll.js"></script>
