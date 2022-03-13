<?php
if ( empty( $provider_id ) ) {
	return;
}
$is_rotatable    = PMC_Ads::get_instance()->get_ad_property( 'is-ad-rotatable', $ad );
$ad_refresh_time = PMC_Ads::get_instance()->get_ad_property( 'ad-refresh-time', $ad );
?>
<div class="adm-column-1">
	<div class="adm-input">
		<label for="<?php echo esc_attr( $provider_id . '-is-ad-rotatable' ); ?>">
			<strong><?php esc_html_e( 'Is refreshable?', 'pmc-adm' ); ?></strong>
		</label>
		<select name="is-ad-rotatable" id="<?php echo esc_attr( $provider_id . '-is-ad-rotatable' ); ?>">
			<?php foreach ( array( 'YES', 'NO' ) as $rotatable_condition ) : ?>
				<option
						value="<?php echo esc_attr( $rotatable_condition ); ?>"
					<?php selected( $is_rotatable, $rotatable_condition ); ?>>
					<?php echo esc_html( $rotatable_condition ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="adm-input">
		<label for="<?php echo esc_attr( $provider_id . '-ad-refresh-time' ); ?>">
			<strong><?php esc_html_e( 'Ad refresh time', 'pmc-adm' ); ?></strong>
		</label>
		<select name="ad-refresh-time" id="<?php echo esc_attr( $provider_id . '-ad-refresh-time' ); ?>">
			<?php foreach ( [ 0, 45, 40, 35, 30, 25, 20, 15 ] as $time_limit ) : ?>
				<option
						value="<?php echo esc_attr( $time_limit ); ?>"
					<?php selected( $ad_refresh_time, $time_limit ); ?>>
					<?php echo esc_html( $time_limit ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
</div>
