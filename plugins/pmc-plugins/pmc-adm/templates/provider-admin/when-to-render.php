<?php
if ( empty( $provider_id ) ) {
	return;
}
$ad_groups = array(
	'default'               => 'Render by default',
	'interrupt-ads'         => 'Interstitial Trigger',
	'interrupt-ads-gallery' => 'Gallery Interstitial Trigger',
);
$ad_groups = apply_filters( 'pmc-adm-ad-groups', $ad_groups );
$ad_group  = PMC_Ads::get_instance()->get_ad_property( 'ad-group', $ad );
?>
<div class="adm-column-1">
	<div class="adm-input">
		<label for="<?php echo esc_attr( $provider_id ); ?>-ad-group">
			<strong><?php esc_html_e( 'When to render:', 'pmc-plugins' ); ?></strong>
		</label>
		<br/>
		<select name="ad-group" id="<?php echo esc_attr( $provider_id . '-ad-group' ); ?>">
			<?php
			foreach ( $ad_groups as $key => $value ) : ?>
				<option
					value="<?php echo esc_attr( $key ); ?>"
					<?php selected( $ad_group, $key ); ?>>
					<?php echo esc_html( $value ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>
