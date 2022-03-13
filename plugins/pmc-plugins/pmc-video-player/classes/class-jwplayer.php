<?php
/**
 * Configuration for JW player event tracking.
 *
 * @author Vinod Tella <vtella@pmc.com>
 *
 * @since 2018-03-06 READS-1032
 */

namespace PMC\Video_Player;

use PMC\Video_Player\Video;
use PMC\Global_Functions\Traits\Singleton;
use PMC;

class JWPlayer {

	use Singleton;

	/**
	 * Calling required hooks.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_action( 'init', [ $this, 'action_init' ] );
	}

	public function action_init() {

		if ( is_admin() ) {
			// We do not want to activate in wp-admin
			return;
		}

		add_action( 'wp', [ $this, 'action_wp' ] );
		add_filter( 'jwplayer_js_embed', [ $this, 'filter_jwplayer_js_embed' ] );

		// Make sure the priority is low to make sure we override the theme filter
		add_filter( 'script_loader_tag', [ $this, 'filter_script_loader_tag' ], 99999, 2 );
		add_filter( 'pmc_core_scripts_remove_defer', [ $this, 'filter_pmc_core_scripts_remove_defer' ] );
		add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ) );
	}

	public function action_wp() {
		$this->_maybe_override_shortcode();
	}

	public function action_wp_enqueue_scripts() {

		if ( is_admin() ) {
			return;
		}

		wp_enqueue_script( 'pmc-jwplayer', pmc_maybe_minify_url( 'js/pmc-jwplayer.js', __DIR__ ), [ 'jquery' ], PMC_VIDEO_PLAYER_VERSION );

		$jwplayer_player       = get_option( 'jwplayer_player' ) ?: 'ALJ3XQCI'; // phpcs:ignore
		$jwplayer_player_cgid  = \PMC_Cheezcap::get_instance()->get_option( 'pmc_video_player_catapultx_group_id' );
		$jwplayer_content_mask = get_option( 'jwplayer_content_mask' ) ?: 'content.jwplatform.com'; // phpcs:ignore
		$jw_enabled            = apply_filters( 'pmc_video_player_remove_jw_scripts', true );
		$jwplayer_player       = apply_filters( 'pmc_jwplayer_id', $jwplayer_player );
		$comscore_publisher_id = apply_filters( 'comscore_publisher_id', 6035310 );

		$pmc_jwplayer_options = [
			'pid'              => $jwplayer_player,
			'cgid'             => $jwplayer_player_cgid,
			'ads_suppression'  => apply_filters( 'pmc_ads_suppression', false, [ 'jwplayer', 'all' ] ),
			'disable_floating' => false,
		];

		$comscore_enabled = \PMC_Cheezcap::get_instance()->get_option( 'pmc_video_player_jw_player_comscore' );

		if ( ! empty( $comscore_publisher_id ) && ! empty( $comscore_enabled ) ) {
			wp_enqueue_script( 'pmc-jwplayer-streamingtag-plugin', 'https://sb.scorecardresearch.com/c2/plugins/streamingtag_plugin_jwplayer.js' );

			$pmc_meta       = \PMC_Page_Meta::get_page_meta();
			$label_mappings = [
				sprintf( 'c2="%s"', $comscore_publisher_id ),
				sprintf( 'c4="%s"', $pmc_meta['lob'] ),
				sprintf( 'ns_st_st="%s"', $pmc_meta['lob'] ),
				sprintf( 'ns_st_pu="%s"', $pmc_meta['lob'] ),
				sprintf( 'ns_st_ge="%s"', $pmc_meta['lob_genre'] ),
				'ns_st_tdt="*null"',
				'ns_st_ddt="*null"',
			];

			$pmc_jwplayer_options['comscore'] = [
				'publisherId'  => $comscore_publisher_id,
				'labelmapping' => implode( ',', $label_mappings ),
			];
		}

		$pmc_jwplayer_options = apply_filters(
			'pmc_jwplayer_options',
			$pmc_jwplayer_options
		);

		wp_localize_script( 'pmc-jwplayer', 'pmc_jwplayer_options', $pmc_jwplayer_options );

		if ( ! empty( $jwplayer_player_cgid ) ) {
			wp_enqueue_script( 'pmc-video-player-catapultx', 'https://tags.catapultx.com/bootstrapper' );
		}

		if ( $jw_enabled ) {
			// @TODO: prevent wp jwplayer plugin from including our default player
			// Disable this for now as RS, SJ & SK is overriding this script and enqueue its owner player
			// Theme should be using the filter above to disable the default jwplayer library and not use deregister
			// $GLOBALS['jwplayer_shortcode_embedded_players'][] = $jwplayer_player;
			wp_enqueue_script( 'pmc-video-player-library', esc_url( sprintf( 'https://%1$s/libraries/%2$s.js', $jwplayer_content_mask, $jwplayer_player ) ) );
		}

		if ( Video::is_jwplayer_ga_enabled() ) {

			$js_ext     = ( \PMC::is_production() ) ? '.min.js' : '.js';
			$script_url = sprintf( '%sjs/ga-jwplayer%s', PMC_VIDEO_PLAYER_URL, $js_ext );

			wp_enqueue_script( 'pmc-jwplayer-ga-js', $script_url, [ 'jquery' ], 1.1, true );

		}

	}

	/**
	 * Helper function to render the jwplayer div tag
	 * @param string $media      The video id
	 * @param string $json_feed  The json feed url
	 * @param string $player     The jwplayer id
	 * @param array $params      The options - reserved for future use
	 * @param bool $echo         To echo the html or not
	 * @return string
	 */
	public function render_tag( string $media, string $json_feed = '', string $player = '', array $params = [], bool $echo = true ) : string {

		global $jwplayer_shortcode_embedded_players;

		if ( empty( $player ) ) {
			$player = get_option( 'jwplayer_player' );
		}

		$content_mask = jwplayer_get_content_mask();

		if ( empty( $json_feed ) ) {
			$json_feed = "https://$content_mask/feeds/$media.json";
		}

		$safe_html = '';
		if ( ! in_array( $player, (array) $jwplayer_shortcode_embedded_players, true ) ) {
			$jwplayer_shortcode_embedded_players[] = $player;

			$js_lib     = 'https://' . $content_mask . '/libraries/' . $player . '.js';
			$safe_html .= sprintf( '<script onload="pmc_jwplayer.add();" type="text/javascript" src="%s"></script>', esc_url( $js_lib ) ); // phpcs:ignore
		}

		$safe_html .= sprintf( '<div id="jwplayer_%1$s_%2$s_div" data-videoid="%1$s" data-player="%2$s" data-jsonfeed="%3$s"></div>', esc_attr( $media ), esc_attr( $player ), esc_url( $json_feed ) );

		if ( $echo ) {
			echo $safe_html; // phpcs:ignore
		}

		return $safe_html;

	}

	public function filter_jwplayer_js_embed( $js_embed ) {
		// @TODO: to be remove and integrate with AMP pages
		if ( ! \PMC::is_amp() ) {

			// Escaping the patterns var = jwplayer(..).setup(..) to avoid potential breaking jwplayer script custom code
			// eg. player = jwplayer('div').setup({}); player.on('ready',....);
			$js_embed = preg_replace( '/(=\s*|\.)\b(jwplayer)\b\s*\(/', '\1__esc_jwplayer__(', $js_embed );

			// Replacing known patterns generated from jwplayer shortcode only
			$js_embed = preg_replace( '/\b(?:jwplayer)\b\(([^)]+)\).setup\(/', 'pmc_jwplayer(\1).setup(', $js_embed );

			// Restore the escaped jwplayer patterns
			$js_embed = preg_replace( '/(=\s*|\.)__esc_jwplayer__\(/', '\1jwplayer(', $js_embed );

			$js_embed = preg_replace( '/<script (type=\'text\/javascript\'.*? src=\'[^\']+jwp[^\']+\.com\/libraries\/.*?\.js)/', '<script onload="pmc_jwplayer.add();" $1', $js_embed ); // phpcs:ignore

		}
		return $js_embed;
	}

	// We need this filter here to override theme defer;
	// some plugin should not be defer and should be able to set by pmc plugin
	public function filter_script_loader_tag( $tag, $handle ) {
		$excluded = apply_filters( 'pmc_core_scripts_remove_defer', [] );
		if ( ! empty( $excluded ) ) {
			if ( in_array( $handle, (array) $excluded, true ) ) {
				$tag = str_replace( "defer='defer'", "", $tag ); // phpcs:ignore
			}
		}
		if ( false === strpos( $tag, 'onload="' ) ) {
			if ( false !== strpos( $tag, 'jwplatform.com/libraries' ) ) {
				$tag = preg_replace( '/<script(.*? src=\'[^\']+jwplatform\.com\/libraries\/.*?\.js)/', '<script onload="pmc_jwplayer.add();" $1', $tag ); // phpcs:ignore
			} elseif ( 'pmc-jwplayer-streamingtag-plugin' === $handle ) {
				$tag = preg_replace( '/<script /', '<script async onload="pmc_jwplayer.comscore.tracking.onload();" ', $tag ); // phpcs:ignore
			}
		}
		return $tag;
	}

	public function filter_pmc_core_scripts_remove_defer( $scripts_not_defer ) {
		$excluded = [
			'pmc-jwplayer',
		];
		return array_merge( (array) $excluded, (array) $scripts_not_defer );
	}

	private function _maybe_override_shortcode() {
		if ( PMC::is_amp() ) {
			return;
		}

		remove_shortcode( 'jwplayer' );
		remove_shortcode( 'jwplatform' );

		$shortcode_handler = function( $atts ) {

			// @ticket ROP-2240, @see pmc-jwplayer.js corresponding code pmc_jwplayer.get_position
			// We're using vloc here to avoid conflict with pmc_position defined in contextual player
			if ( ! isset( $atts['player'] ) && ! isset( $atts['pmc_position'] ) && ! isset( $atts['vloc'] ) ) {
				$atts['vloc'] = 'auto';
				if ( isset( $atts[0] ) ) {
					$patterns = '/(?P<media>[0-9a-z]{8})(?:[-_])?(?P<player>[0-9a-z]{8})?/i';
					preg_match( $patterns, $atts[0], $matches );
					if ( $matches['player'] ) {
						$atts['vloc'] = '';
					}
				}
			}

			// @TODO: Remove when pmc-variety-2020 theme no longer reference jw-player-1.5.1
			// @codeCoverageIgnoreStart
			if ( defined( 'JWPLAYER_PLUGIN_VERSION' ) && '1.5.1' === JWPLAYER_PLUGIN_VERSION ) {

				// We can't affective test this code due to multiple jwplayer wp plugin version
				return apply_filters( 'jwplayer_js_embed', jwplayer_shortcode_handle( $atts ) );
			}
			// @codeCoverageIgnoreEnd

			return jwplayer_shortcode_handle( $atts );

		};

		add_shortcode( 'jwplayer', $shortcode_handler );
		add_shortcode( 'jwplatform', $shortcode_handler );

	}

}
