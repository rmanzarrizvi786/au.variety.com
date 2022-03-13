<?php
/**
 * Template for related link
 */

$title = __( 'Related', 'pmc-apple-news' );

if ( ! empty( $items['title'] ) ) {
	$title = $items['title'];
}

unset( $items['title'] );

// This need to check after getting title.
// After fetching (unset) title. there will be only post data in array.
// So after that if array is empty mean there is not posts to show so bail out.
if ( empty( $items ) || ! is_array( $items ) ) {
	return;
}

?>
<p>
	<strong><?php echo esc_html( $title ); ?></strong>
</p>

<?php
foreach ( $items as $index => $item ) {
	if ( empty( $item['url'] ) || empty( $item['title'] ) ) {
		continue;
	}
	?>
	<p>
		<a href="<?php echo esc_url( $item['url'] ); ?>" target="_blank"><?php echo esc_html( wp_strip_all_tags( $item['title'] ) ); ?></a>
	</p>
	<?php
}
?>
