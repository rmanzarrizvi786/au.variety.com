<?php
/**
 * Display author information.
 *
 * @since 2017-01-31 Chandra Patel CDWE-124
 */

global $post;

$authors = array();

if ( ! empty( $GLOBALS['post']->ID ) && intval( $GLOBALS['post']->ID ) > 0 ) {
	$authors = PMC::get_post_authors( $post->ID, 'all', array( 'ID', 'display_name', 'type' ) );
}

if ( ! empty( $authors ) ) {
?>

<div class="amp-wp-meta amp-wp-byline">
	<?php
	// Display author image if only one author.
	if ( 1 === count( $authors ) ) {

		$author = array_values( $authors )[0];

		if ( ! empty( $author['type'] ) && 'guest-author' === $author['type'] ) {
			$avatar_url = get_the_post_thumbnail_url( $author['ID'], 'guest-author-32' );
		} else {
			$avatar_url = get_avatar_url( $author['ID'], array( 'size' => 24 ) );
		}

		if ( ! empty( $avatar_url ) ) {
			printf( '<amp-img src="%s" width="24" height="24" layout="fixed"></amp-img>', esc_url( $avatar_url ) );
		}

	}

	$display_names = wp_list_pluck( $authors, 'display_name' );
	$glue          = __( 'and', 'pmc-google-amp' );
	?>

	<span class="amp-wp-author author vcard">
		<?php echo esc_html( implode( " {$glue} ", $display_names ) ); ?>
	</span>
</div>

<?php
}
