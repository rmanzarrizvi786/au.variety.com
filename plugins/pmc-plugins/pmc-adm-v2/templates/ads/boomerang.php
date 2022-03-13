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
$zone                  = ( ! empty( $ad['zone'] ) ) ? rtrim( ltrim( $ad['zone'], '/' ), '/' ) : '';
$sizes                 = ( ! empty( $ad['ad-width'] ) ) ? $ad['ad-width'] : '';

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

if ( ! empty( $ad['css-slot-rotate'] ) ) {
	$css_class .= ' ' . $ad['css-slot-rotate'];
}
?>

<div style="<?php echo esc_attr( $ad['css-style'] ); ?>"
	class="pmc-adm-boomerang-pub-div <?php echo esc_attr( $ad['css-class'] ); ?>"
	data-priority="<?php echo esc_attr( $ad['priority'] ); ?>"
>
	<div id="<?php echo esc_attr( $ad['div-id'] ); ?>"
		class="<?php echo esc_attr( $css_class ); ?>" data-is-adhesion-ad="<?php echo $is_adhesion_ad_unit ? true : false; ?>">
		<script type="application/javascript">
			blogherads.adq.push(function () {
				<?php if ( $is_adhesion_ad_unit ) { ?>
				blogherads
					.defineSlot( '<?php echo esc_attr( $ad['ad-display-type'] ); ?>', 'skm-ad-bottom', <?php echo wp_json_encode( $targeting_data ); ?> )
				<?php } else { ?>
				blogherads
					.defineSlot( '<?php echo esc_attr( $ad['ad-display-type'] ); ?>', '<?php echo esc_attr( $ad['div-id'] ); ?>' )
				<?php } ?>
				<?php
				foreach ( $targeting_data as $key => $value ) {

					if ( is_array( $value ) ) {
						printf( ".setTargeting( '%s', %s )\n", esc_js( $key ), wp_json_encode( $value ) );
					} else {
						printf( ".setTargeting( '%s', '%s' )\n", esc_js( $key ), esc_js( $value ) );
					}

				}
				?>
				<?php if ( ! empty( $zone ) ) { ?>
				.setSubAdUnitPath(<?php echo wp_json_encode( $zone ); ?>)
				<?php } ?>
				<?php if ( ! empty( $sizes ) && 'reskin' !== $ad['ad-display-type'] ) { ?>
				.addSize(<?php echo wp_json_encode( $sizes ); ?>)
				<?php } ?>
				<?php if ( isset( $ad['is_lazy_load'] ) && 'yes' === $ad['is_lazy_load'] ) { ?>
				.setLazyLoadMultiplier(2)
				<?php } ?>
				;
				<?php if ( false !== strpos( $zone, 'leaderboard' ) ) { ?>
				blogherads.setFirstSlot(<?php echo wp_json_encode( $ad['div-id'] ); ?>);
				<?php } ?>
			});
		</script>
	</div>
</div>
