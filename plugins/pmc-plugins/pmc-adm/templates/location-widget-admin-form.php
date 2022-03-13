<?php
/**
 * Template for displaying the Ad Location widget admin form
 * Called within class-pmc-ads-location-widget.php
 */
?>
<p>
	<label for="<?php echo esc_attr( $provider_field_id ); ?>">
		<?php esc_html_e( 'Providers', 'pmc-plugins' ); ?>
	</label>
	<select
		id="<?php echo esc_attr( $provider_field_id ); ?>"
		name="<?php echo esc_attr( $provider_field_name ); ?>"
		class="widefat">
		<option value=""><?php esc_html_e( 'Choose One', 'pmc-plugins' ); ?></option>
		<?php foreach ( $providers as $provider_key ) : ?>
			<option
				value="<?php echo esc_attr( $provider_key ); ?>"
				<?php selected( $selected_provider, $provider_key ) ?>>
				<?php echo esc_html( $provider_key ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</p>
<p>
	<label for="<?php echo esc_attr( $ad_location_field_id ); ?>">
		<?php esc_html_e( 'Ad Location', 'pmc-plugins' ); ?>
	</label>
	<span class="loading-ad-location"></span>
	<select
		id="<?php echo esc_attr( $ad_location_field_id ); ?>"
		name="<?php echo esc_attr( $ad_location_field_name ); ?>"
		class="widefat">
		<option value=""><?php esc_html_e( 'Choose One', 'pmc-plugins' ); ?></option>

		<?php foreach ( $ad_locations as $location_slug => $location_name ) : ?>
			<option
				value="<?php echo esc_attr( $location_slug ); ?>"
				<?php selected( $selected_ad_location, $location_slug ) ?>>
				<?php echo esc_html( $location_name ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</p>
<p>
	<label for="<?php echo esc_attr( $wrap_div_class_field_id ); ?>">
		<?php esc_html_e( 'Wrap div with following class', 'pmc-plugins' ); ?>
	</label>
	<input
		type="text"
		id="<?php echo esc_attr( $wrap_div_class_field_id ); ?>"
		name="<?php echo esc_attr( $wrap_div_class_field_name ); ?>"
		class="widefat"
		value="<?php echo esc_attr( $selected_wrap_div_class ); ?>"/>
</p>

<script type="text/javascript">

jQuery(document).ready( function() {

	var provider_select_field_id = '<?php echo esc_js( "#" . $provider_field_id ); ?>';
	var location_select_field_id = '<?php echo esc_js( "#" . $ad_location_field_id ); ?>';
	var location_field = jQuery(location_select_field_id);
	var provider_field = jQuery(provider_select_field_id);
	var location_span = jQuery('.loading-ad-location');

	provider_field.change(function () {

		var provider = provider_field.val();

		location_field.hide();
		location_span.empty().append( 'Loading...' ).show();

		if ( '' !== provider ) {

			jQuery.ajax({
				type: 'POST',
				url: '<?php echo esc_js( esc_url( $admin_url ) ); ?>',
				dataType: 'json',
				data: {
					'action': 'get_locations_for_provider',
					'provider': provider,
					'<?php echo esc_js( $nonce_key ); ?>': '<?php echo esc_js( $nonce_field ); ?>'
				},
				success: function (response) {

					if( response.success ) {

						location_field.empty();

						jQuery("<option value=''>Choose One</option>").appendTo(location_field);

						jQuery.each(response.data.locations, function (key, text) {

							location_field.append(jQuery('<option>',
								{
									value: key,
									text: text
								}
							));

						});

						location_field.show();
						location_span.empty().hide();

					} else {
						location_span.append('There were some errors fetching the locations' + response.data.error );
					}

				}

			});

		}

	});
});
</script>
