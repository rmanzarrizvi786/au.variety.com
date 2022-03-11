<?php
/**
 * Template part for Scorecard Settings.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-09-01 Milind More CDWE-499
 */

?>
<style>
#variety_scorecard_settings_form input[type='text'] {
	width: 600px;
}
#variety_scorecard_settings_form .form-table th {
	width: 150px;
}
</style>
<div class="wrap">
	<h2><?php esc_html_e( 'Pilots Scorecard Settings', 'pmc-variety' ); ?></h2>
	<form action="options.php" method="post" id="variety_scorecard_settings_form">
		<?php
		settings_fields( 'variety_scorecard_settings' );
		do_settings_sections( 'variety_scorecard_settings' );
		submit_button( __( 'Save Options', 'pmc-variety' ) );
		// we only want to do the auto synchronize if setting is saved
		if ( ! empty( $settings_updated ) && true === $settings_updated ) {
			Variety_Scorecard::get_instance()->fetch_modified_time( true );
		}
		?>
	</form>
</div>
