<?php
if ( empty( $provider_id ) ) {
	return;
}
$devices = PMC_Ads::get_instance()->get_ad_property( 'device', $ad );
$devices = ( empty( $devices ) || ! is_array( $devices ) ) ? array( 'Desktop' ) : $devices;

?>
<div class="adm-column-1">
	<div class="adm-input">
		<label class="required">
			<strong><?php esc_html_e( 'Show this ad on:', 'pmc-plugins' ); ?></strong>
		</label>
		<br>
		<?php
		foreach ( array( 'Desktop', 'Tablet', 'Mobile' ) as $device ) : ?>
			<label for="<?php echo esc_attr( $provider_id . '-' . $device ); ?>">
				<input
					type="checkbox"
					name="device[]"
					id="<?php echo esc_attr( $provider_id . '-' . $device ); ?>"
					value="<?php echo esc_attr( $device ); ?>"
					<?php checked( in_array( $device, $devices, true ), true ); ?>>
				&nbsp;
				<?php echo esc_html( $device ); ?>
			</label><br>
			<?php
		endforeach;

		unset( $devices );
		?>
	</div>
</div>