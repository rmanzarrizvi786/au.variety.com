<?php
/**
 * Video
 *
 * Responsible for video related functionality. Extends Variety Top Video functionality.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Variety_VIP;

use \PMC\Global_Functions\Traits\Singleton;
use Variety\Inc\Carousels;

/**
 * Class Video
 */
class Video {

	use Singleton;

	/**
	 * Carousel name for featured VIP video.
	 */
	const ARCHIVE_CAROUSEL = 'vip-featured-video';

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() {

		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 10, 1 );
		add_action( 'pre_get_posts', [ $this, 'video_archive_query' ] );
		add_action( 'fm_term_variety_vip_playlist', [ $this, 'add_term_fields' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 11 );

	}

	/**
	 * Adds the Video Meta Box from Variety Top Video to VIP Videos.
	 *
	 * @param string $post_type The post type of the meta box.
	 */
	public function add_meta_boxes( $post_type ) {

		$top_video_plugin = \Variety_Top_Videos::get_instance();

		if ( Content::VIP_VIDEO_POST_TYPE === $post_type ) {

			// Add the Variety Top Video metabox.
			add_meta_box( 'variety-top-video-link', esc_html__( 'Video Information', 'pmc-variety' ), [ $top_video_plugin, 'top_video_information_meta_box' ], $post_type, 'normal' );

		}

	}

	/**
	 * Modify the VIP video archive query to account for featured/curated videos.
	 * @codeCoverageIgnore
	 *
	 * @param \WP_Query $query The WP Query object.
	 *
	 * @return \WP_Query
	 */
	public function video_archive_query( $query ) {
		if ( ! is_admin() && $query->is_main_query() && ( is_post_type_archive( Content::VIP_VIDEO_POST_TYPE ) || is_tax( Content::VIP_PLAYLIST_TAXONOMY ) ) ) {
			$query->set( 'posts_per_page', 16 );
		}

		return $query;
	}

	/**
	 * Add term fields for uploading a featured image.
	 *
	 * @return \Fieldmanager_Context_Term
	 * @throws \FM_Developer_Exception
	 *
	 * @codeCoverageIgnore
	 */
	public function add_term_fields() {

		$fm = new \Fieldmanager_Group(
			[
				'name'     => 'variety_playlist_featured_image',
				'children' => [
					'featured_image' => new \Fieldmanager_Media(
						[
							'name'         => 'featured_image',
							'button_label' => esc_html__( 'Set Featured Image ', 'pmc-variety' ),
							'modal_title'  => esc_html__( 'Featured Image ', 'pmc-variety' ),
							'mime_type'    => 'image',
						]
					),
				],
			]
		);

		return $fm->add_term_meta_box( esc_html__( 'Featured Image', 'pmc-variety' ), Content::VIP_PLAYLIST_TAXONOMY );

	}

	/**
	 * Fetch the latest VIP videos
	 *
	 * @param int      $count How many to return.
	 * @param \WP_Term $term A term to fetch videos from.
	 *
	 * @return \WP_Query
	 */
	public static function get_latest_video( $count = 5, $term = null ) {
		$video_query = [
			'post_type'      => Content::VIP_VIDEO_POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => $count,
			'offset'         => 0,
		];

		if ( ! empty( $term ) ) {
			$video_query['tax_query'] = [ // @codingStandardsIgnoreLine: Usage of tax_query is required as we need post from video playlist taxonomy.
				[
					'taxonomy' => $term->taxonomy,
					'terms'    => $term->term_id,
				],
			];
		}

		return new \WP_Query( $video_query );
	}

	/**
	 * @codeCoverageIgnore
	 * Enqueue JWPlayer Scripts.
	 */
	public function enqueue_assets() {

		if ( ! is_singular() ) {
			return;
		}

		$video_source = get_post_meta( get_queried_object_id(), 'variety_top_video_source', true );

		if ( empty( $video_source ) ) {
			$video_source = get_post_meta( get_queried_object_id(), '_pmc_featured_video_override_data', true );
			if ( empty( $video_source ) ) {
				return;
			}
		}

		if ( strpos( $video_source, 'jwplayer' ) !== false || strpos( $video_source, 'jwplatform' ) !== false ) {

			global $jwplayer_shortcode_embedded_players;

			$regex = '/\[jwplayer (?P<media>[0-9a-z]{8})(?:[-_])?(?P<player>[0-9a-z]{8})?\]/i';
			preg_match( $regex, $video_source, $matches, null, 0 );

			$player = ( ! empty( $matches['player'] ) ) ? $matches['player'] : false;
			$player = ( false === $player ) ? get_option( 'jwplayer_player' ) : $player;

			$content_mask = \jwplayer_get_content_mask();
			$protocol     = ( is_ssl() && defined( 'JWPLAYER_CONTENT_MASK' ) && JWPLAYER_CONTENT_MASK === $content_mask ) ? 'https' : 'http';

			if ( false !== $player && ! in_array( $player, (array) $jwplayer_shortcode_embedded_players, true ) ) {
				$js_lib = "$protocol://$content_mask/libraries/$player.js";

				$jwplayer_shortcode_embedded_players[] = $player;
				wp_enqueue_script( 'variety-vip-jwscript-' . $player, $js_lib, [], '', true );
			}
		}
	}

}

// EOF.
