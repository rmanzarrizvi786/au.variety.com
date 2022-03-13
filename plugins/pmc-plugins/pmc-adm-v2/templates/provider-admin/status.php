<?php
if ( empty( $provider_id ) ) {
	return;
}
$status = PMC_Ads::get_instance()->get_ad_property( 'status', $ad );
?>
<div class="adm-column-2">
	<div class="adm-input">
		<label for="<?php echo esc_attr( $provider_id . '-status' ); ?>">
			<strong><?php esc_html_e( 'Status:', 'pmc-plugins' ); ?></strong>
		</label>
		<br/>
		<select name="status" id="<?php echo esc_attr( $provider_id . '-status' ); ?>">
			<?php foreach ( array( 'Active', 'Disable' ) as $status_item ) : ?>
				<option
					value="<?php echo esc_attr( $status_item ); ?>"
					<?php selected( $status, $status_item ); ?>>
					<?php echo esc_html( $status_item ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
</div>