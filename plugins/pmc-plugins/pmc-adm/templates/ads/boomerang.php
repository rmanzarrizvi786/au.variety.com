<?php
/**
 * Boomerang Ad template
 *
 */

$targeting_data = [];

foreach ( $ad['targeting_data'] as $data ) {

	if ( empty( $targeting_data[ $data['key'] ] ) ) {
		$targeting_data[ $data['key'] ] = $data['value'];
	} elseif ( is_array( $targeting_data[ $data['key'] ] ) ) {
		$targeting_data[ $data['key'] ][] = $data['value'];
	} else {
		$targeting_data[ $data['key'] ] = [
			$targeting_data[ $data['key'] ],
			$data['value'],
		];
	}

}


$out_of_page = ( empty( $ad['out-of-page'] ) || 'no' === strtolower( $ad['out-of-page'] ) ) ? false : true;

$ad['ad-display-type'] = ( ! empty( $ad['ad-display-type'] ) ) ? $ad['ad-display-type'] : '';
$ad['div-id']          = ( ! empty( $ad['div-id'] ) ) ? $ad['div-id'] : '';

$is_adhesion_ad_unit = false;

$adhesion_ad_units = [
	'desktop-bottom-sticky-ad',
	'mobile-bottom-sticky-ad',
];

if ( ! empty( $ad['location'] ) && in_array( $ad['location'], (array) $adhesion_ad_units, true ) ) {
	$is_adhesion_ad_unit = true;
}

$css_class = '';
if ( ! in_array( $ad['ad-display-type'], [ 'nativemini', 'nativecontent', 'nativesidebar' ] ) ) {
	$css_class = sprintf( 'adw-%s adh-%s', $ad['width'], $ad['height'] );
}

?>

<div style="<?php echo esc_attr( $ad['css-style'] ); ?>"
	class="pmc-adm-boomerang-pub-div <?php echo esc_attr( $ad['css-class'] ); ?>">
	<div id="<?php echo esc_attr( $ad['div-id'] ); ?>"
		class="<?php echo esc_attr( $css_class ); ?>" data-is-adhesion-ad="<?php echo $is_adhesion_ad_unit ? true : false; ?>">
		<script type="application/javascript">
			blogherads.adq.push(function () {
				<?php if ( $is_adhesion_ad_unit ) { ?>
				blogherads
					.defineSlot( '<?php echo esc_attr( $ad['ad-display-type'] ); ?>', '<?php echo esc_attr( $ad['div-id'] ); ?>', <?php echo wp_json_encode( $targeting_data ); ?> )
					.display();
				<?php } else { ?>
				blogherads
					.defineSlot( '<?php echo esc_attr( $ad['ad-display-type'] ); ?>', '<?php echo esc_attr( $ad['div-id'] ); ?>' )
					<?php
					foreach ( $targeting_data as $key => $value ) {

						if ( is_array( $value ) ) {
							printf( ".setTargeting( '%s', %s )\n", esc_js( $key ), wp_json_encode( $value ) );
						} else {
							printf( ".setTargeting( '%s', '%s' )\n", esc_js( $key ), esc_js( $value ) );
						}
					}
					?>
					.display();
				<?php } ?>
			});
		</script>
	</div>
</div>
