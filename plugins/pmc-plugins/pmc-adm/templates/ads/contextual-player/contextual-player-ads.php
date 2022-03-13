<?php
/**
 * Page template to auto inject related jwplayer videos into page.
 */

if ( empty( $player_id ) || empty( $playlist_id ) ) {
	return;
}

$playlist_url = sprintf( 'https://cdn.jwplayer.com/v2/playlists/%s?semantic=true&backfill=true&search=__CONTEXTUAL__', esc_attr( $playlist_id ) );
$player_url   = sprintf( 'https://content.jwplatform.com/libraries/%s.js', esc_attr( $player_id ) );
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
			var jwConfig = {
				playlist: <?php echo wp_json_encode( $playlist_url ); ?>,
				width: "100%",
				aspectratio: "16:9",
				mute: true,
				pmc_position: <?php echo wp_json_encode( $position ); ?>,
				floating: <?php echo wp_json_encode( $floating ); ?>,
				<?php if ( ! empty( $enable_shelf_widget ) && 'yes' === $enable_shelf_widget ) { ?>
				related: {
					autoplaytimer: 10,
					displayMode: "shelfWidget",
					onclick: "link",
					oncomplete: "autoplay"
				},
				<?php } ?>
			};

			if ( 'function' === typeof window.pmc_jwplayer ) {
				window.pmc_jwplayer.add();
				window.contextual_player = window.pmc_jwplayer('jwplayer_contextual_player_div').setup(jwConfig).instance();
			} else if ('function' === typeof jwplayer) {
				window.contextual_player = jwplayer('jwplayer_contextual_player_div').setup(jwConfig);
			}

		}
	</script>
	<script onload="buildJW()" type="text/javascript" src=<?php echo esc_url( $player_url ); ?>></script>
</div>
