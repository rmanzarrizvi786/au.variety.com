<?php
/**
 * Template for config settings of Contextual Player ads for Ad-manager.
 *
 * @package pmc-adm
 */

if ( empty( $provider_id ) ) {
	return;
}

$player_title        = PMC_Ads::get_instance()->get_ad_property( 'contextual-player-title', $ad );
$playlist_id         = PMC_Ads::get_instance()->get_ad_property( 'playlist-id', $ad );
$player_id           = PMC_Ads::get_instance()->get_ad_property( 'contextual-player-id', $ad );
$player_position     = PMC_Ads::get_instance()->get_ad_property( 'contextual-player-position', $ad );
$enable_shelf_widget = PMC_Ads::get_instance()->get_ad_property( 'contextual-enable-shelf-widget', $ad );

?>
<div class="adm-column-2 contextual-player hidden">
	<fieldset class="adm-input">
		<legend>
			<strong><?php esc_html_e( 'Contextual Matching Player:', 'pmc-adm' ); ?></strong>
		</legend>
		<div>

			<label for="<?php echo esc_attr( $provider_id . '-contextual-player-title' ); ?>">
				<strong><?php esc_html_e( 'Call to action', 'pmc-adm' ); ?></strong>
			</label>
			<br>
			<input
				type="text"
				name="contextual-player-title"
				id="<?php echo esc_attr( $provider_id . '-contextual-player-title' ); ?>"
				placeholder="Title for contextual player"
				class="contextual-player-title"
				value="<?php echo esc_attr( $player_title ); ?>">
			<br>

			<label for="<?php echo esc_attr( $provider_id . '-playlist-id' ); ?>">
				<strong><?php esc_html_e( 'Media/Playlist ID', 'pmc-adm' ); ?></strong>
			</label>
			<br>
			<input
				type="text"
				name="playlist-id"
				id="<?php echo esc_attr( $provider_id . '-playlist-id' ); ?>"
				placeholder="JWPlayer Playlist ID"
				class="contextual-player-playlist-id"
				value="<?php echo esc_attr( $playlist_id ); ?>">
			<br>

			<label for="<?php echo esc_attr( $provider_id . '-contextual-player-id' ); ?>">
				<strong><?php esc_html_e( 'Player ID', 'pmc-adm' ); ?></strong>
			</label>
			<br>
			<input
				type="text"
				name="contextual-player-id"
				id="<?php echo esc_attr( $provider_id . '-contextual-player-id' ); ?>"
				placeholder="JWPlayer Player ID"
				class="contextual-player-id"
				value="<?php echo esc_attr( $player_id ); ?>">
			<br>

			<label for="<?php echo esc_attr( $provider_id . '-player-position' ); ?>">
				<strong><?php esc_html_e( 'Contextual Player Position', 'pmc-adm' ); ?></strong>
			</label>
			<br>
			<select id="contextual-player-position" name="contextual-player-position">
				<option value="bottom" <?php selected( $player_position, 'bottom' ); ?>><?php esc_html_e( 'Bottom of Article', 'pmc-adm' ); ?></option>
				<option value="mid" <?php selected( $player_position, 'mid' ); ?>><?php esc_html_e( 'Middle of Article', 'pmc-adm' ); ?></option>
			</select>
			<br>

			<label for="<?php echo esc_attr( $provider_id . '-enable-carousel' ); ?>">
				<strong><?php esc_html_e( 'Add Carousel to contextual player', 'pmc-adm' ); ?></strong>
			</label>
			<br>
			<select id="contextual-enable-shelf-widget" name="contextual-enable-shelf-widget">
				<option value="no" <?php selected( $enable_shelf_widget, 'no' ); ?>><?php esc_html_e( 'No', 'pmc-adm' ); ?></option>
				<option value="yes" <?php selected( $enable_shelf_widget, 'yes' ); ?>><?php esc_html_e( 'Yes', 'pmc-adm' ); ?></option>
			</select>

		</div>
	</fieldset>
</div>

