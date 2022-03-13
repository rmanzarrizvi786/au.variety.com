<?php
/**
 * This class handles the (jwPlayer) Contextual matching player ad that needs to go out-of-page
 *
 * @author jignesh Nakrani <jignesh.nakrani@rtcamp.com>
 *
 * @since 2019-07-01
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Ads_Contextual_Player_Ad {

	use Singleton;

	/**
	 * @var PMC_Ads To store current contextual player ad.
	 */
	protected $_ad;

	/**
	 * This function fires off when the object of this class is created.
	 * Hook up stuff in here
	 *
	 * @return void
	 */
	protected function __construct() {

		/*
		 * Filters
		 */
		add_filter( 'pmc_adm_locations', [ $this, 'add_contextual_player_ad_location' ] );

		// Adding at 11 priority, To make sure inline content ads inject first
		// And then contextual player markup
		add_filter( 'pmc_inject_content_paragraphs', [ $this, 'inject_contextual_player_ad_markup' ], 11 );
		add_filter( 'the_content', [ $this, 'inject_contextual_player_top_bottom' ] );

	}

	/**
	 * Helper function to check if contextual player is eligible to serve or not
	 *
	 * @return bool True when contextual player is allow to render on the page else false
	 */
	protected function _should_contextual_player_render() {

		global $post;

		// If it's not single post types. bail out.
		if ( ! is_feed() && is_single() && true === apply_filters( 'pmc_contextual_player_enable', true ) ) {

			$video_meta = get_post_meta( $post->ID, '_pmc_featured_video_override_data', true );
			$this->_ad  = $this->get_contextual_player_ad();

			/**
			 * Bail out if post has jwplayer featured video.
			 * Bail out if post-content have jwplayer shortcode.
			 */
			if ( ! has_shortcode( $post->post_content, 'jwplayer' ) && strpos( $video_meta, 'jwplayer' ) === false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Inject Contextual player markup
	 **/
	public function inject_contextual_player_ad_markup( $paragraphs = [] ) {

		global $post;

		if ( $this->_should_contextual_player_render() ) {

			$this->_ad = $this->get_contextual_player_ad();

			/**
			 * Inject Contextual player at mid of the article
			 */
			if (
				! empty( $this->_ad )
				&& is_array( $this->_ad )
				&& isset( $this->_ad['contextual-player-position'] )
				&& 'mid' === $this->_ad['contextual-player-position']
			) {

				$clean_content = $this->clean_up_content( $post->post_content );
				$content_array = explode( '</p>', $clean_content );
				$char_count    = 0;

				/**
				 * Inject Contextual player at middle of the article, After the 1st Mid article ad.
				 */
				$first_ad_pos       = \PMC_Cheezcap::get_instance()->get_option( 'pmc-ad-placeholders-first-pos' );
				$injection_args_pos = ( ! empty( $first_ad_pos ) && 0 < intval( $first_ad_pos ) ) ? intval( $first_ad_pos ) + 100 : 500;

				/**
				 * Definitions:
				 * 'pos'      = number of characters before displaying the injection
				 * 'inserted' = if this injection has already been used
				 * 'callback' = a non-static callback function located within this class
				 *
				 * Note that 1 word ≈ 6 characters.
				 *
				 * first ad.
				 */
				$injection_args = [
					'related' => [
						'pos'      => $injection_args_pos,
						'inserted' => false,
						'callback' => [ $this, 'get_contextual_player_ad_markup' ],
					],
				];

				foreach ( $content_array as $index => $content ) {

					$content = strip_shortcodes( $content );

					if ( ! empty( $content ) ) {
						$char_count = $char_count + ( strlen( $content ) - 3 );
						foreach ( $injection_args as $label => $atts ) {
							if ( $char_count > $atts['pos'] && true !== $atts['inserted'] ) {
								$injection_args[ $label ]['inserted'] = true;

								if ( ! empty( $atts['callback'] ) && is_callable( $atts['callback'] ) ) {
									$paragraphs[ $index + 2 ][] = call_user_func( $atts['callback'] );
								}
							}
						}
					}
				}
			}
		}

		return $paragraphs;
	}

	/**
	 * Inject Contextual player markup at top or bottom of post content
	 *
	 * @param $post_content string
	 *
	 * @return string $post_content
	 */
	public function inject_contextual_player_top_bottom( $post_content ) {

		if ( $this->_should_contextual_player_render() ) {

			$this->_ad = $this->get_contextual_player_ad();

			/**
			 * Inject Contextual player at bottom of the article
			 */
			if (
				! empty( $this->_ad )
				&& is_array( $this->_ad )
				&& isset( $this->_ad['contextual-player-position'] )
				&& ( 'bottom' === $this->_ad['contextual-player-position'] || 'top' === $this->_ad['contextual-player-position'] )
			) {

				if ( 'top' === $this->_ad['contextual-player-position'] ) {
					$post_content = $this->get_contextual_player_ad_markup() . $post_content;
				}

				if ( 'bottom' === $this->_ad['contextual-player-position'] ) {
					$post_content .= $this->get_contextual_player_ad_markup();
				}

			}
		}

		return $post_content;
	}

	/**
	 * Get Contextual player markup
	 **/
	public function get_contextual_player_ad_markup() {

		$this->_ad = $this->get_contextual_player_ad();

		if ( empty( $this->_ad ) || ! is_array( $this->_ad ) ) {
			return;
		}

		$media_id    = '';
		$playlist_id = '';

		if ( ! empty( $this->_ad['contextual-player-media-id'] ) ) {
			$media_id = $this->_ad['contextual-player-media-id'];
		}

		if ( ! empty( $this->_ad['contextual-player-playlist-id'] ) ) {
			$playlist_id = $this->_ad['contextual-player-playlist-id'];
		} elseif ( ! empty( $this->_ad['playlist-id'] ) ) {  // ROP-2544: Backward compatible
			$playlist_id = $this->_ad['playlist-id'];
		}

		$config          = [
			'media_id'            => $media_id,
			'player_id'           => empty( $this->_ad['contextual-player-id'] ) ? get_option( 'jwplayer_player' ) : $this->_ad['contextual-player-id'],
			'playlist_id'         => $playlist_id,
			'player_title'        => empty( $this->_ad['contextual-player-title'] ) ? '' : $this->_ad['contextual-player-title'],
			'enable_shelf_widget' => empty( $this->_ad['contextual-enable-shelf-widget'] ) ? '' : $this->_ad['contextual-enable-shelf-widget'],
			'floating'            => $this->is_floating_player_enabled(),
		];
		$ad_teplate_path = ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) ? PMC_ADM_DIR . '/templates/ads/contextual-player/amp-contextual-player-ads.php' : PMC_ADM_DIR . '/templates/ads/contextual-player/contextual-player-ads.php';

		$ad_template = apply_filters( 'pmc_contextual_player_template', $ad_teplate_path );

		return apply_filters( 'jwplayer_js_embed', PMC::render_template( $ad_template, $config, false ) );

	}

	/**
	 * Fetch the Contextual matching player ads configured and return ads if ready for render.
	 *
	 * @return array|bool returns Ads config array if ready to render else false
	 */
	function get_contextual_player_ad() {

		if ( is_admin() || ! is_single() ) {
			return false;
		}

		$ad_location = ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) ? 'amp-contextual-matching-player-ad' : 'contextual-matching-player-ad';

		$ads = PMC_Ads::get_instance()->get_ads_to_render( $ad_location );

		if ( ! empty( $ads ) && is_array( $ads ) ) {
			$ads = array_shift( $ads );
		}

		return $ads;
	}

	/**
	 * Add ad location for contextual player ad
	 *
	 * @param $locations array
	 *
	 * @return mixed
	 */
	public function add_contextual_player_ad_location( $locations = [] ) {

		$locations['contextual-matching-player-ad'] = [
			'title'     => esc_html__( 'Contextual Matching Player Ad', 'pmc-adm' ),
			'providers' => [ 'google-publisher', 'boomerang' ],
		];

		$locations['amp-contextual-matching-player-ad'] = [
			'title'     => esc_html__( 'AMP Contextual Matching Player Ad', 'pmc-adm' ),
			'providers' => [ 'google-publisher', 'boomerang' ],
		];

		return $locations;
	}

	/**
	 * Clean up Content
	 * Helper function to Strips shortcodes and tags from content,
	 * leaving only paragraphs.
	 *
	 * @param string $content Some content.
	 *
	 * @return string Cleaned content.
	 */
	public function clean_up_content( $content ) {
		$content = wpautop( $content );
		$content = strip_tags( $content, '<p>' );
		$content = preg_replace( '|^<p>(https?://[^\s"]+)</p>$|im', '\1', $content );

		return $content;
	}

	/**
	 * Check if Floating Video feature is enabled in mobile.
	 *
	 * @return boolean
	 */
	public function is_floating_player_enabled() {

		$enabled = false;

		if ( class_exists( 'PMC\Floating_Video\Setup' ) ) {

			$floating_video = PMC\Floating_Video\Setup::get_instance();

			if ( $floating_video->is_enabled() ) {
				$enabled = true;
			}
		}

		return $enabled;
	}
}

