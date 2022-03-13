<?php
if ( empty( $provider_id ) ) {
	return;
}
$lazy_load = PMC_Cheezcap::get_instance()->get_option( 'pmc_enable_disable_lazy_load' );
$is_lazy_load = PMC_Ads::get_instance()->get_ad_property( 'is_lazy_load', $ad );
?>
<div class="adm-column-3">
	<fieldset class="adm-input adm-lazy-load">
		<?php if ( 'disable' === $lazy_load ) : ?>
			<p style="color: #ff0000"><?php esc_html_e( 'Lazy Load is currently DISABLED', 'pmc-plugins' ); ?></p>
		<?php endif; ?>
		<legend>
			<strong><?php esc_html_e( 'Lazy Load This Ad', 'pmc-plugins' ); ?></strong>
		</legend>
		<div class="adm-input">
			<label for="<?php echo esc_attr( $provider_id . '-lazy-load' ); ?>">
				<strong><?php esc_html_e( 'Enable', 'pmc-plugins' ); ?></strong>
			</label>
			<select name="lazy-load" id="<?php echo esc_attr( $provider_id . '-lazy-load' ); ?>">
				<option value="no" <?php selected( $is_lazy_load, 'no' ); ?>>
					<?php esc_html_e( 'No', 'pmc-plugins' ); ?>
				</option>
				<option value="yes" <?php selected( $is_lazy_load, 'yes' ); ?>>
					<?php esc_html_e( 'Yes', 'pmc-plugins' ); ?>
				</option>
			</select>
		</div>
	</fieldset>
</div>