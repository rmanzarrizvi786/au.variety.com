<?php
/**
 * Class to handle Video player companion ads
 *
 * @author Vinod Tella <vtella@pmc.com>
 *
 * @since 2018-04-10 READS-1136
 */

namespace PMC\Video_Player;

use PMC\Global_Functions\Traits\Singleton;

class Video_Ads {
	use Singleton;

	protected function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_action( 'pmc_tags_head', [ $this, 'load_ias_header_script' ] );
		\PMC\Preload\Scripts\Ad_Hoc::add( 'https://static.adsafeprotected.com/vans-adapter-google-ima.js' );
	}

	/**
	 * Enqueue JS script to handle video ad request.
	 */
	public function enqueue_scripts() {

		// Only proceed if we're not in the admin
		if ( is_admin() ) {
			return;
		}

		$script_url = pmc_maybe_minify_url( 'js/video-ads.js', __DIR__ );
		wp_enqueue_script( 'pmc-video-player-ads-js', $script_url, [ 'jquery', 'underscore' ], PMC_VIDEO_PLAYER_VERSION, true );

		$localize_data = [
			'is_jwplayer_cc_enabled' => absint( cheezcap_get_option( 'pmc_video_player_jw_player_cc' ) ),
		];

		wp_localize_script( 'pmc-video-player-ads-js', 'pmc_video_player_ads', $localize_data );
	}

	/**
	 * Load IAS Header script.
	 */
	public function load_ias_header_script() {
		if ( ! defined( 'PMC_ADM_V2' ) ) {
			\PMC::render_template( PMC_VIDEO_PLAYER_ROOT . '/templates/ias-tags.php', [], true );
		}
	}

}
