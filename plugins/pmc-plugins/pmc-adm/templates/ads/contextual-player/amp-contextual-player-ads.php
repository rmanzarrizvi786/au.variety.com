<?php
/**
 * Page template to auto inject related jwplayer videos into page.
 */

if ( empty( $player_id ) || empty( $playlist_id ) ) {
	return;
}

$args = [
	'data-player-id'   => $player_id,
	'data-playlist-id' => $playlist_id,
	'layout'           => 'responsive',
	'width'            => 16,
	'height'           => 9,
];

if ( 'nodock' !== \PMC_Cheezcap::get_instance()->get_option( 'pmc_amp_jwplayer_docking' ) ) {
	$args['dock'] = true;
}
?>

<div class="pmc-contextual-player">
	<?php if ( ! empty( $player_title ) ) { ?>
		<h3>
			<?php echo esc_html( $player_title ); ?>
		</h3>
	<?php } ?>

	<?php \PMC\Google_Amp\Plugin::get_instance()->render_jwplayer_tag( $args ); ?>

</div>
