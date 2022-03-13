<?php
/**
 * Template for config settings of floating preroll ads for Ad-manager.
 *
 * @package pmc-adm
 */

if ( empty( $provider_id ) ) {
	return;
}

$media_id      = PMC_Ads::get_instance()->get_ad_property( 'media-id', $ad );
$player_id     = PMC_Ads::get_instance()->get_ad_property( 'player-id', $ad );
$cap_frequency = PMC_Ads::get_instance()->get_ad_property( 'cap-frequency', $ad );

?>
<div class="adm-column-2 floating-preroll hidden">
	<fieldset class="adm-input">
		<legend>
			<strong><?php esc_html_e( 'Floating Preroll Ad:', 'pmc-adm' ); ?></strong>
		</legend>
		<div>
			<label for="<?php echo esc_attr( $provider_id . '-media-id' ); ?>">
				<strong><?php esc_html_e( 'Media/Playlist ID', 'pmc-adm' ); ?></strong>
			</label>
			<br>
			<input
				type="text"
				name="media-id"
				id="<?php echo esc_attr( $provider_id . '-media-id' ); ?>"
				placeholder="JWPlayer Media ID"
				class="floating-preroll-media-id"
				value="<?php echo esc_attr( $media_id ); ?>">
			<br>
			<label for="<?php echo esc_attr( $provider_id . '-player-id' ); ?>">
				<strong><?php esc_html_e( 'Player ID', 'pmc-adm' ); ?></strong>
			</label>
			<br>
			<input
				type="text"
				name="player-id"
				id="<?php echo esc_attr( $provider_id . '-player-id' ); ?>"
				placeholder="JWPlayer Player ID"
				class="floating-preroll-player-id"
				value="<?php echo esc_attr( $player_id ); ?>">
			<br>
			<label for="<?php echo esc_attr( $provider_id . '-cap-frequency' ); ?>">
				<strong><?php esc_html_e( 'Cap Frequency', 'pmc-adm' ); ?></strong>
				<?php esc_html_e( '(in hours)', 'pmc-adm' ); ?>
			</label>
			<br>
			<input
				type="number"
				name="cap-frequency"
				id="<?php echo esc_attr( $provider_id . '-cap-frequency' ); ?>"
				min="0"
				value="<?php echo esc_attr( $cap_frequency ); ?>"
				title="<?php esc_attr_e( 'Number of hours before ad is displayed again', 'pmc-adm' ); ?>">
		</div>
	</fieldset>
</div>

