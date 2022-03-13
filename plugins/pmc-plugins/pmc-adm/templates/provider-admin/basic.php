<?php
if ( empty( $provider_id ) || empty( $provider_locations ) || ! is_array( $provider_locations ) ) {
	return;
}

$ad_title = ( ! empty( $ad ) && ! empty( $ad->post_title ) ) ? $ad->post_title : '';
$width = PMC_Ads::get_instance()->get_ad_property( 'width', $ad );
$height = PMC_Ads::get_instance()->get_ad_property( 'height', $ad );
$priority = PMC_Ads::get_instance()->get_ad_property( 'priority', $ad, 10 );
$css_class = PMC_Ads::get_instance()->get_ad_property( 'css-class', $ad );
$current_location = PMC_Ads::get_instance()->get_ad_property( 'location', $ad );
$current_location = ( empty( $current_location ) && ! empty( $ad ) ) ? 'widget' : $current_location;

if ( ! empty( $current_location ) && ! in_array( $current_location, array_keys( $provider_locations ), true ) ) {
	$provider_locations[ $current_location ] = $current_location . ' (invalid)';
}

?>
<div class="adm-column-1">
	<div class="adm-input form-required">
		<label for="<?php echo esc_attr( $provider_id . '-title' ); ?>" class="required">
			<strong><?php esc_html_e( 'Title', 'pmc-plugins' ); ?></strong>
		</label>
		<br>
		<input
			type="text"
			name="title"
			id="<?php echo esc_attr( $provider_id . '-title' ); ?>"
			placeholder="Anything (require)"
			value="<?php echo esc_attr( $ad_title ); ?>">
	</div>

	<div class="adm-input">
		<label for="<?php echo esc_attr( $provider_id . '-location' ); ?>" class="required">
			<strong><?php esc_html_e( 'Location', 'pmc-plugins' ); ?></strong>
		</label>
		<br>
		<select
			name="location"
			id="<?php echo esc_attr( $provider_id . '-location' ); ?>"
			class="max-width-80-pc">
			<?php foreach ( $provider_locations as $location_val => $location ) : ?>
				<option
					value="<?php echo esc_attr( $location_val ); ?>"
					<?php selected( $location_val, $current_location ); ?>>
					<?php echo esc_html( $location ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="adm-input form-required">
		<label for="<?php echo esc_attr( $provider_id . '-width' ); ?>" class="required">
			<strong><?php esc_html_e( 'Width', 'pmc-plugins' ); ?></strong>
		</label>
		<br>
		<input
			type="text"
			name="width"
			id="<?php echo esc_attr( $provider_id . '-width' ); ?>"
			placeholder="300 (require)"
			value="<?php echo esc_attr( $width ); ?>">
	</div>
	<div class="adm-input form-required">
		<label for="<?php echo esc_attr( $provider_id . '-height' ); ?>" class="required">
			<strong><?php esc_html_e( 'Height', 'pmc-plugins' ); ?></strong>
		</label>
		<br>
		<input
			type="text"
			name="height"
			id="<?php echo esc_attr( $provider_id . '-height' ); ?>"
			placeholder="250 (require)"
			value="<?php echo esc_attr( $height ); ?>">
	</div>

	<div class="adm-input form-required">
		<label for="<?php echo esc_attr( $provider_id . '-priority' ); ?>">
			<strong><?php esc_html_e( 'Priority', 'pmc-plugins' ); ?></strong>
		</label>
		<br>
		<input
			type="number"
			name="priority"
			id="<?php echo esc_attr( $provider_id . '-priority' ); ?>"
			size="1"
			value="<?php echo esc_attr( $priority ); ?>">
		<br>
		<span
			class="description"><?php esc_html_e( 'Lower number has higher priority. 9 will override 10', 'pmc-plugins' ); ?></span>
	</div>
	<div class="adm-input">
		<label for="<?php echo esc_attr( $provider_id . '-css-class' ); ?>">
			<strong><?php esc_html_e( 'CSS class (optional)', 'pmc-plugins' ); ?></strong>
		</label>
		<br>
		<input
			type="text"
			name="css-class"
			id="<?php echo esc_attr( $provider_id . '-css-class' ); ?>"
			size="20"
			placeholder="top-ad__sidebar"
			value="<?php echo esc_attr( $css_class ); ?>">
	</div>
</div>