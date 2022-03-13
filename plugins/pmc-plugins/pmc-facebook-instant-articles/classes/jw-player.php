<?php
namespace PMC\Facebook_Instant_Articles;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Generate jw player video embed for Facebook Instant Article
 *
 * NOTE: FBIA plugin already using transient to cache the_content result
 *
 * Class JW_Player
 * @package PMC\Facebook_Instant_Articles
 */
class JW_Player {
	use Singleton;

	private $_backup_shortcodes = [];

	function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_action( 'init', [ $this, 'action_init' ] );
	}

	public function action_init() {
		add_action( 'instant_articles_before_transform_post', [ $this, 'action_instant_articles_before_transform_post' ] );
		add_action( 'instant_articles_after_transform_post', [ $this, 'action_instant_articles_after_transform_post' ] );
	}

	/**
	 * Action to trigger before article transform into instant article object
	 */
	public function action_instant_articles_before_transform_post() {
		global $shortcode_tags;
		foreach ( [ 'jwplayer', 'jwplatform' ] as $name ) {
			if ( isset( $shortcode_tags[ $name ] ) ) {
				$this->_backup_shortcodes[ $name ] = $shortcode_tags[ $name ];
			}
		}
		remove_shortcode( 'jwplayer' );
		remove_shortcode( 'jwplatform' );

		add_shortcode( 'jwplayer', [ $this, 'do_shortcode' ] );
		add_shortcode( 'jwplatform', [ $this, 'do_shortcode' ] );

		Plugin::get_instance()->add_rules(
			[
				'.op-interactive' => 'PassThroughRule',
			]
		);
	}

	/**
	 * Action to trigger after article had been transformed into instance article object
	 */
	public function action_instant_articles_after_transform_post() {
		foreach ( $this->_backup_shortcodes as $name => $callback ) {
			add_shortcode( $name, $callback );
		}
		$this->_backup_shortcodes = [];
	}

	/**
	 * Processing the jw player shortcode for instance article
	 *
	 * @param $atts
	 * @return string
	 */
	public function do_shortcode( $atts ) : string {

		if ( isset( $atts[0] ) ) {
			// @see jwplayer_shortcode_handle
			$patterns = '/(?P<media>[0-9a-z]{8})(?:[-_])?(?P<player>[0-9a-z]{8})?/i';
			$matches  = [];
			if ( preg_match( $patterns, $atts[0], $matches ) ) {
				if ( function_exists( 'jwplayer_get_content_mask' ) ) {
					$content_mask = jwplayer_get_content_mask();
				}
				if ( empty( $content_mask ) ) {
					$content_mask = 'content.jwplatform.com';
				}
				$feed_url     = sprintf( 'https://%s/feeds/%s.json', $content_mask, $matches['media'] );
				$data         = $this->get_data_from_json_feed( $feed_url );
				$video_source = $this->extract_video_source( $data );
				return $this->generate_html( $video_source );
			}
		}

		return '';
	}

	/**
	 * Helper function to extract the video from jwplayer json playlist
	 * @param array $data
	 * @return array
	 */
	public function extract_video_source( array $data ) : array {

		// Set the max video size to extract from a list of video with multiple sizes
		$max_width  = 720;
		$max_height = 480;
		$video      = [];

		if ( ! empty( $data['playlist'][0]['sources'] ) ) {
			foreach ( $data['playlist'][0]['sources'] as $source ) {
				if ( preg_match( '/video/', $source['type'] ) ) {
					if (
						empty( $video )
						|| (
							(
								$source['height'] <= $max_height
								&& (
									$source['height'] > $video['height']
									|| $video['height'] > $max_height
								)
							)
							&& (
								$source['width'] <= $max_width
								&& (
									$source['width'] > $video['width']
									|| $video['width'] > $max_width
								)
							)
						)
					) {
						$video = $source;
					}
				}
			}
		}
		return $video;
	}

	/**
	 * Generate the instance article video html content
	 *
	 * @param array $video_source
	 * @return string
	 */
	public function generate_html( array $video_source ) : string {
		if ( ! empty( $video_source ) ) {
			return sprintf( '<figure class="op-interactive"><iframe src="%s" width="%d" height="%d"></iframe></figure>', esc_url( $video_source['file'] ), esc_attr( $video_source['width'] ), esc_attr( $video_source['height'] ) );
		}
		return '';
	}

	/**
	 * Helper function to retrieve the jwplayer playlist json feed url
	 * @param string $feed_url
	 * @return array
	 */
	public function get_data_from_json_feed( string $feed_url ) : array {
		$response = vip_safe_wp_remote_get( $feed_url );
		if ( ! empty( $response ) && ! is_wp_error( $response ) ) {
			return (array) json_decode( $response['body'], true );
		}
		return [];
	}

}


