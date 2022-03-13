<?php
/**
 * AMP next page feature.
 *
 * @package pmc-google-amp
 * @since   2019-23-19 - Sayed Taqui
 * @ticket  ROP-1994
 */

if ( empty( $next_page_data ) || ! is_array( $next_page_data ) ) {
	return;
}

?>

<div class="amp-next-page-container">
	<amp-next-page>
		<script type="application/json">
			<?php
			if ( \PMC\Google_Amp\Plugin::get_instance()->is_at_least_version( '2.0.4' ) ) {
				echo wp_json_encode( $next_page_data['pages'] );
			} else {
				echo wp_json_encode( $next_page_data );
			}
			?>
		</script>
	</amp-next-page>
</div>
