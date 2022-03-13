<?php
/**
 * Configuration for youtube player event tracking.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since 2018-03-01 READS-923
 */

namespace PMC\Video_Player;

use PMC\Video_Player\Video;
use PMC\Global_Functions\Traits\Singleton;

class YTPlayer {

	use Singleton;

	/**
	 * Class instantiation.
	 */
	protected function __construct() {

		if ( is_admin() ) {
			return;
		}

		$this->_setup_hooks();
	}

	/**
	 * Method to setup listeners to hooks
	 *
	 * @return void
	 *
	 */
	protected function _setup_hooks() {

		/**
		 * Actions.
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'pmc_tags_head', array( $this, 'load_youtube_iframe_api' ) );

		/**
		 * Filters.
		 */
		add_filter( 'video_embed_html', array( $this, 'filter_youtube_embed_iframe_url' ) );

	}

	/**
	 * Enqueue assets for event tracking.
	 */
	public function enqueue_scripts() {

		if ( Video::is_ytplayer_enabled() ) {
			$js_ext = ( \PMC::is_production() ) ? '.min.js' : '.js';

			$script_url = sprintf( '%s/js/ga-ytplayer%s', PMC_VIDEO_PLAYER_URL, $js_ext );

			wp_register_script( 'pmc-ytplayer-event-tracking-js', $script_url, array( 'jquery', 'pmc-ga-event-tracking' ), PMC_VIDEO_PLAYER_VERSION, true );
			wp_enqueue_script( 'pmc-ytplayer-event-tracking-js' );
		}
	}

	/**
	 * Load Youtube iframe api in head.
	 */
	public function load_youtube_iframe_api() {
		if ( Video::is_ytplayer_enabled() ) {
			\PMC::render_template( PMC_VIDEO_PLAYER_ROOT . '/templates/head-tags.php', [], true );
		}
	}

	/**
	 * Add enablejsapi=1 parameter to support youtube embed with iframe api.
	 *
	 * @param string $html shortcode html string.
	 *
	 * @return string $html
	 */
	public function filter_youtube_embed_iframe_url( $html ) {

		if ( Video::is_ytplayer_enabled() && false !== strpos( $html, 'youtube.com/embed' ) ) {
			$html = str_replace( '?version=3', '?version=3&enablejsapi=1&origin=' . site_url(), $html );
		}

		return $html;
	}
}
