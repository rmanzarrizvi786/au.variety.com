<?php
/**
 * This class handles the (jwPlayer) Floating preroll ad that needs to go out-of-page
 *
 * @author jignesh Nakrani <jignesh.nakrani@rtcamp.com>
 *
 * @since 2018-10-01
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Ads_Floating_Preroll_Ad {

	use Singleton;

	/**
	 * @var PMC_Ads To store current floating preroll ad.
	 */
	protected $_ad;

	/**
	 *
	 * This function fires off when the object of this class is created.
	 * Hook up stuff in here
	 *
	 * @return void
	 */
	protected function __construct() {
		add_action( 'pmc-tags-top', array( $this, 'action_add_floating_preroll_ad_markup' ) );
		add_action( 'wp_head', array( $this, 'localize_floating_preroll_ad_data' ), 10 );

		/*
		 * Filters
		 */
		add_filter( 'pmc_adm_locations', array( $this, 'add_preroll_ad_location' ) );
	}

	/**
	 * Renders floating preroll player markup
	 **/
	public function action_add_floating_preroll_ad_markup() {

		$this->_ad = $this->get_floating_preroll_ad();

		if ( empty( $this->_ad ) || ! is_array( $this->_ad ) ) {
			return;
		}

		if ( ! empty( $this->_ad['floating-player-id'] ) ) {
			$player_id = $this->_ad['floating-player-id'];
		} else {
			// Backward compatible code
			$player_id = empty( $this->_ad['player-id'] ) ? '' : $this->_ad['player-id'];
		}

		$ad_template = PMC_ADM_DIR . '/templates/ads/floating/jwplayer-video-preroll-ad.php';

		PMC::render_template( $ad_template, [ 'player_id' => $player_id ], true );

	}

	/**
	 * Fetch the floating pre-roll ads configured and return ads if ready for render.
	 *
	 * @return array|bool returns Ads config array if ready to render else false
	 */
	function get_floating_preroll_ad() {

		if ( is_admin() || ! is_single() ) {
			return false;
		}

		global $post;

		$video_meta = get_post_meta( $post->ID, '_pmc_featured_video_override_data', true );

		/**
		 * Bail out if post has jwplayer featured video.
		 * Bail out if post-content have jwplayer shortcode.
		 */
		if ( has_shortcode( $post->post_content, 'jwplayer' ) || strpos( $video_meta, 'jwplayer' ) !== false ) {
			return false;
		}

		$ads = PMC_Ads::get_instance()->get_ads_to_render( 'floating-video-preroll-ad' );

		if ( ! empty( $ads ) && is_array( $ads ) ) {
			$ads = array_shift( $ads );
		}

		return $ads;
	}

	/**
	 * localizing Floating preroll ad data.
	 */
	public function localize_floating_preroll_ad_data() {

		$this->_ad = $this->get_floating_preroll_ad();

		if ( empty( $this->_ad ) || ! is_array( $this->_ad ) ) {
			return;
		}

		$media_id    = '';
		$playlist_id = '';

		if ( ! empty( $this->_ad['floating-player-media-id'] ) ) {
			$media_id = $this->_ad['floating-player-media-id'];
		} elseif ( ! empty( $this->_ad['media-id'] ) ) { // Backward compatible code
			$media_id = $this->_ad['media-id'];
		}

		if ( ! empty( $this->_ad['floating-player-playlist-id'] ) ) {
			$playlist_id = $this->_ad['floating-player-playlist-id'];
		}

		$time_gap    = empty( $this->_ad['cap-frequency'] ) ? 0 : intval( $this->_ad['cap-frequency'] ) * 3600;
		$cookie_name = 'pmc-ads-' . md5( 'floating-preroll-ad' . $this->_ad['ID'] );

		$data = [
			'cookie_name' => $cookie_name,
			'media_id'    => $media_id,
			'playlist_id' => $playlist_id,
			'time_gap'    => apply_filters( 'pmc_adm_floating_preroll_ad_time_gap', $time_gap ),
		];

		PMC_Scripts::add_script( 'pmcadm_floating_preroll_data', $data, 'wp_head', 10 );

	}

	/**
	 * Add ad location for preroll video ad
	 *
	 * @param $locations array
	 *
	 * @return mixed
	 */
	public function add_preroll_ad_location( $locations = [] ) {

		$locations['floating-video-preroll-ad'] = [
			'title'     => __( 'Floating Preroll Ad', 'pmc-adm' ),
			'providers' => [ 'google-publisher', 'boomerang' ],
		];

		return $locations;
	}
}
