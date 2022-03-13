<?php
$header_tag       = $item[1];
$atts             = (array) shortcode_parse_atts( $item[2] );
$text             = $item[3];
$id_attr          = $item[4];
$atts_string_safe = '';
$default_atts     = [
	'class'    => 'pmc-toc--heading lrv-a-show-on-hover-parent',
	'id'       => $id_attr,
	'tabindex' => '-1',
];

if ( $atts['class'] ) {
	$atts['class'] = trim( $atts['class'] ) . ' ' . $default_atts['class'];
}

$atts = array_merge( $default_atts, $atts );

foreach ( $atts as $key => $value ) {
	if ( is_numeric( $key ) ) {
		continue;
	}

	$atts_string_safe .= sprintf( ' %s="%s"', sanitize_key( $key ), esc_attr( $value ) );
}

if ( ! empty( $i ) && ! empty( $jump_to_top ) ) {
	?>
	<p class="pmc-toc--jump-to-top lrv-u-font-size-12 lrv-u-text-align-right">
		<a href="#">
			<?php esc_html_e( 'Top', 'pmc-toc' ); ?>
		</a>
	</p>
	<?php
}
// Opening header_tag and attributes.
printf( '<%s%s>', sanitize_key( $header_tag ), $atts_string_safe ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>
<a class="pmc-toc--anchor lrv-u-position-relative" href="<?php echo esc_url( '#' . $id_attr ); ?>" title="<?php echo esc_attr( wp_strip_all_tags( $text ) ); ?>" aria-hidden="true">
		<span class="pmc-toc--anchor-icon lrv-a-show-on-hover">
			<svg class="xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1.25rem" height="1.25rem" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 20 20"><path d="M17.74 2.76a4.321 4.321 0 0 1 0 6.1l-1.53 1.52c-1.12 1.12-2.7 1.47-4.14 1.09l2.62-2.61l.76-.77l.76-.76c.84-.84.84-2.2 0-3.04a2.13 2.13 0 0 0-3.04 0l-.77.76l-3.38 3.38c-.37-1.44-.02-3.02 1.1-4.14l1.52-1.53a4.321 4.321 0 0 1 6.1 0zM8.59 13.43l5.34-5.34c.42-.42.42-1.1 0-1.52c-.44-.43-1.13-.39-1.53 0l-5.33 5.34c-.42.42-.42 1.1 0 1.52c.44.43 1.13.39 1.52 0zm-.76 2.29l4.14-4.15c.38 1.44.03 3.02-1.09 4.14l-1.52 1.53a4.321 4.321 0 0 1-6.1 0a4.321 4.321 0 0 1 0-6.1l1.53-1.52c1.12-1.12 2.7-1.47 4.14-1.1l-4.14 4.15c-.85.84-.85 2.2 0 3.05c.84.84 2.2.84 3.04 0z" fill="#626262"/></svg>
		</span>
</a>
<?php
echo wp_kses_post( $text );
// Closing header_tag.
printf( '</%s>', sanitize_key( $header_tag ) );
?>
