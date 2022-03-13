<?php
/**
 * Template for boomerang ads for Ad-manager.
 *
 * @package pmc-adm
 */

if ( empty( $provider ) || ! is_a( $provider, 'Boomerang_Provider' ) ) {
	return;
}

?>
<div class="adm-column-2">

	<?php
	foreach ( $provider->get_fields() as $field => $data ) :
		if ( is_string( $data ) ) {
			$data = array( 'title' => $data );
		}

		$data            = array_merge(
			[
				'required'  => true,
				'validator' => '',
			],
			$data
		);
		$default         = ( ! empty( $data['default'] ) ) ? $data['default'] : '';
		$div_css_class   = ( $data['required'] ) ? 'adm-input form-required' : 'adm-input';
		$label_css_class = ( $data['required'] ) ? 'required' : '';
		?>
		<div class="<?php echo esc_attr( $div_css_class ); ?>">
			<label for="<?php echo esc_attr( $provider_id . '-' . $field ); ?>" class="<?php echo esc_attr( $label_css_class ); ?>">
				<strong><?php echo esc_html( $data['title'] ); ?></strong>
			</label>
			<br>
			<?php if ( ! empty( $data['options'] ) ) : ?>
				<select name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $provider_id . '-' . $field ); ?>">
					<?php foreach ( $data['options'] as $k => $v ) : ?>
						<option
							value="<?php echo esc_attr( $k ); ?>"
							<?php selected( PMC_Ads::get_instance()->get_ad_property( $field, $ad, $default ), $k ); ?>>
							<?php echo esc_html( $v ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			<?php else : ?>
				<input type="text"
						validator="<?php echo esc_attr( $data['validator'] ); ?>"
						name="<?php echo esc_attr( $field ); ?>"
						id="<?php echo esc_attr( $provider_id . '-' . $field ); ?>"
						placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>"
						value="<?php echo esc_attr( PMC_Ads::get_instance()->get_ad_property( $field, $ad, $default ) ); ?>">
			<?php endif; ?>
		</div>

	<?php endforeach; ?>
</div>
