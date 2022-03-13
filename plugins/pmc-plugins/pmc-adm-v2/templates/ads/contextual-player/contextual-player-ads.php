<?php
/**
 * Page template to auto inject related jwplayer videos into page.
 */

if ( empty( $player_id ) || ( empty( $playlist_id ) && empty( $media_id ) ) ) {
	return;
}

if ( ! empty( $playlist_id ) ) {
	$playlist_url = sprintf( 'https://cdn.jwplayer.com/v2/playlists/%s?semantic=true&backfill=true&search=__CONTEXTUAL__', $playlist_id );
}
else {
	$playlist_url = sprintf( 'https://cdn.jwplayer.com/v2/media/%s?semantic=true&backfill=true&search=__CONTEXTUAL__', $media_id );
}

$player_url   = sprintf( 'https://content.jwplatform.com/libraries/%s.js', $player_id );

$jw_config = [
	'playlist'    => $playlist_url,
	'width'       => '100%',
	'aspectratio' => '16:9',
	'mute'        => true,
	'floating'    => $floating,
];

if ( ! empty( $enable_shelf_widget ) && 'yes' === $enable_shelf_widget ) {
	$jw_config['related'] = [
		'autoplaytimer' => 10,
		'displayMode'   => 'shelfWidget',
		'onclick'       => 'link',
		'oncomplete'    => 'autoplay',
	];
}

// Do not add extra newlines to the JS below.
// Newlines get converted to paragraph tags, which breaks the rendered JS
?>

<div class="pmc-contextual-player">
	<?php if ( ! empty( $player_title ) ) { ?>
		<h3>
			<?php echo esc_html( $player_title ); ?>
		</h3>
	<?php } ?>

	<div id="jwplayer_contextual_player_div"></div>
	<?php if ( ! empty( $enable_shelf_widget ) && 'yes' === $enable_shelf_widget ) { ?>
		<div id="jwplayer_contextual_player_div-shelf-widget"></div>
	<?php } ?>

	<script type="text/javascript">
		function buildJW() {
			var jwConfig = <?php echo wp_json_encode( $jw_config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>;
			if ( 'function' === typeof window.pmc_jwplayer ) {
				window.pmc_jwplayer.add();
				window.contextual_player = window.pmc_jwplayer('jwplayer_contextual_player_div').setup(jwConfig).instance();
			} else if ('function' === typeof window.jwplayer) {
				window.contextual_player = window.jwplayer('jwplayer_contextual_player_div').setup(jwConfig);
			}
		}
	</script>
	<script onload="buildJW()" type="text/javascript" src="<?php echo esc_url( $player_url ); ?>"></script>
</div>
