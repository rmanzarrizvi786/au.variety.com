<h2>API</h2>
<form method="post" action="">
	<?php $nonce->render(); ?>
	<input type="hidden" name="action" value="api">

	<div>
		<label style="width:150px; display:inline-block;text-align:right">Disable the API</label>
		<input type="checkbox" name="api_disabled" value="1" <?php checked( $items['disabled'] ); ?>/>
	</div>
	<div>
		<label style="width:150px; display:inline-block;text-align:right">Use Legacy APP API</label>
		<input type="checkbox" name="api_legacy_app" value="1" <?php checked( $items['legacy_app'] ); ?>/>
	</div>
	<div>
		<label style="width:150px; display:inline-block;text-align:right">API Key/Client Id</label>
		<input style="min-width: 400px" type="text" name="api_key" value="<?php echo esc_attr( $items['key'] ); ?>"/>
	</div>
	<div>
		<label style="width:150px; display:inline-block;text-align:right">API Client Secret</label>
		<input style="min-width: 400px" type="text" name="api_secret" value="<?php echo esc_attr( $items['secret'] ); ?>"/>
	</div>
	<div>
		<label style="width:150px; display:inline-block;text-align:right">Authentication Base URI</label>
		<input style="min-width: 400px" type="text" name="api_base_auth_url" value="<?php echo esc_attr( $items['base_auth_url'] ); ?>"/>
	</div>
	<div>
		<label style="width:150px; display:inline-block;text-align:right">REST Base URI</label>
		<input style="min-width: 400px" type="text" name="api_base_url" value="<?php echo esc_attr( $items['base_url'] ); ?>"/>
	</div>
	<div>
		<label style="width:150px; display:inline-block;text-align:right">SOAP Base URI</label>
		<input style="min-width: 400px" type="text" name="api_base_soap_url" value="<?php echo esc_attr( $items['base_soap_url'] ); ?>"/>
	</div>

	<div style="margin-left: 150px;padding-left: 2px;">
		<input type="submit" value="Update"/>
	</div>
</form>
