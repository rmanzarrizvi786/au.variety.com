<?php
if ( empty( $provider_id ) ) {
	return;
}
$number = PMC_Ads::get_instance()->get_ad_property( 'adunit-order', $ad, 10 );
?>
<div class="adm-column-3">
	<fieldset class="adm-input adm-adunit-order">
		<legend><strong><?php esc_html_e( 'Adunit Order', 'pmc-plugins' ); ?></strong></legend>
		<div class="adm-input">
			<label for="<?php echo esc_attr( $provider_id . '-adunit-order' ); ?>">
				<strong><?php esc_html_e( 'Order', 'pmc-plugins' ); ?></strong>
			</label>
			<br>
			<input
				type="number"
				name="adunit-order"
				id="<?php echo esc_attr( $provider_id . '-adunit-order' ); ?>"
				size="1"
				value="<?php echo esc_attr( $number ); ?>">
			<br>
			<span class="description"><?php esc_html_e( 'Order in the DFP request. Top starts with 1. Default is 10.', 'pmc-plugins' ); ?></span>
		</div>
	</fieldset>
</div>
