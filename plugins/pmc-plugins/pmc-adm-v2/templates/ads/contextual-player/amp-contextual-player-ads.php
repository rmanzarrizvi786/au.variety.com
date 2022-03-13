<?php
/**
 * Page template to auto inject related jwplayer videos into page.
 */

if ( empty( $player_id ) || ( empty( $playlist_id ) && empty( $media_id ) ) ) {
	return;
}

$args = [
	'data-media-id'    => $media_id,
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

	<?php

	// Display player with no ads
	if ( class_exists( '\PMC\Partner_Scroll\Plugin' ) && \PMC\Partner_Scroll\Plugin::get_instance()->is_scroll_enabled() ) {
		$scroll_template = PMC_GOOGLE_AMP_ROOT . '/templates/scroll-player.php';
		\PMC::render_template( $scroll_template, ['args' => $args ], true );
	} else {
		\PMC\Google_Amp\Plugin::get_instance()->render_jwplayer_tag( $args );
	}

	?>
</div>
