<?php
/**
 * Display comments link below bottom social icons and above Outbrain module.
 *
 * @ticket CDWE-96
 * @since  2017-01-19 - Chandra Patel
 */

?>

<div class="amp-comments-link">
	<a href="<?php echo esc_url( $comments_link ); ?>">
		<?php
		if ( empty( $comments_number ) ) {
			esc_html_e( 'Leave a Comment', 'pmc-google-amp' );
		} else {
			printf(
				esc_html( _n( '%d Comment', '%d Comments', $comments_number, 'pmc-google-amp' ) ),
				esc_html( $comments_number )
			);
		}
		?>
	</a>
</div>
