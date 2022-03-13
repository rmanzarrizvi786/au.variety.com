<?php
if ( empty( $provider_id ) ) {
	return;
}
$current_time = PMC_TimeMachine::create( $manager->timezone )->now();
$startDate = PMC_Ads::get_instance()->get_ad_property( 'start', $ad );
$startDate = ( ! empty( $startDate ) ) ? $startDate : '';
$endDate   = PMC_Ads::get_instance()->get_ad_property( 'end', $ad );
$endDate   = ( ! empty( $endDate ) ) ? $endDate : '';
$duration  = PMC_Ads::get_instance()->get_ad_property( 'duration', $ad, 8 );
$time_gap  = PMC_Ads::get_instance()->get_ad_property( 'timegap', $ad, 24 );

?>
<div class="adm-column-2">
	<fieldset class="adm-input adm-timeframe">
		<legend>
			<strong><?php esc_html_e( 'Timeframe: (optional)', 'pmc-plugins' ); ?></strong>
		</legend>
		<span class="description">
			<?php esc_html_e( 'Current Time: ', 'pmc-plugins' ); ?>
			<?php echo esc_html( $current_time ); ?>
		</span>
		<br>
		<div>
			<label for="<?php echo esc_attr( $provider_id . '-start' ); ?>">
				<strong><?php esc_html_e( 'Start Time', 'pmc-plugins' ); ?></strong>
			</label>
			<br>
			<input
				type="text"
				name="start"
				id="<?php echo esc_attr( $provider_id . '-start' ); ?>"
				placeholder="YYYY-MM-DD HH:MM"
				class="timeframe-start"
				value="<?php echo esc_attr( $startDate ); ?>">
			<br>
			<label for="<?php echo esc_attr( $provider_id . '-end' ); ?>">
				<strong><?php esc_html_e( 'End Time', 'pmc-plugins' ); ?></strong>
			</label>
			<br>
			<input
				type="text"
				name="end"
				id="<?php echo esc_attr( $provider_id . '-end' ); ?>"
				placeholder="YYYY-MM-DD HH:MM"
				class="timeframe-end"
				value="<?php echo esc_attr( $endDate ); ?>">
		</div>
	</fieldset>

	<div class="adm-input field-interruptus hidden">
		<label for="<?php echo esc_attr( $provider_id . '-duration' ); ?>">
			<strong><?php esc_html_e( 'Display Duration', 'pmc-plugins' ); ?></strong>
			<?php esc_html_e( ' (in seconds)', 'pmc-plugins' ); ?>
		</label>
		<br>
		<input
			type="number"
			name="duration"
			id="<?php echo esc_attr( $provider_id . '-duration' ); ?>"
			min="0"
			value="<?php echo esc_attr( $duration ); ?>"
			title="<?php esc_attr_e( 'Number of seconds to show ad', 'pmc-plugins' ); ?>">
	</div>

	<div class="adm-input field-interruptus hidden">
		<label for="<?php echo esc_attr( $provider_id . '-timegap' ); ?>">
			<strong><?php esc_html_e( 'Time Gap', 'pmc-plugins' ); ?></strong>
			<?php esc_html_e( '(in hours)', 'pmc-plugins' ); ?>
		</label>
		<br>
		<input
			type="number"
			name="timegap"
			id="<?php echo esc_attr( $provider_id . '-timegap' ); ?>"
			min="0"
			value="<?php echo esc_attr( $time_gap ); ?>"
			title="<?php esc_attr_e( 'Number of hours before ad is displayed again', 'pmc-plugins' ); ?>">
	</div>
</div>

