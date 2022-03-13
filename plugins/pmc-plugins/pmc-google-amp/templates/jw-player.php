<?php
/**
 * Adding the JW Player to AMP Template.
 *
 * @ticket PMCVIP-2489
 * @since 2016-11-02 - Debabrata Karfa
 */

$args = [
	'data-media-id'  => $video_hash,
	'data-player-id' => $player_hash,
	'layout'         => 'responsive',
	'width'          => 16,
	'height'         => 9,
];

if ( 'nodock' !== \PMC_Cheezcap::get_instance()->get_option( 'pmc_amp_jwplayer_docking' ) ) {
	$args['dock'] = true;
}

// Display player with no ads
if ( class_exists( '\PMC\Partner_Scroll\Plugin' ) && \PMC\Partner_Scroll\Plugin::get_instance()->is_scroll_enabled() ) {
	$scroll_template = PMC_GOOGLE_AMP_ROOT . '/templates/scroll-player.php';
	\PMC::render_template( $scroll_template, ['args' => $args ], true );
} else {
	\PMC\Google_Amp\Plugin::get_instance()->render_jwplayer_tag( $args );
}
