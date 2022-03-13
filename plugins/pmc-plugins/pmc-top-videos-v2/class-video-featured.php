<?php
/**
 * Featured Video Category.
 *
 * @package pmc-top-videos-v2
 */

namespace PMC\Top_Videos_V2;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Video Featured widget
 */
class Video_Featured extends \FM_Widget {

	use Singleton;

	const PMC_TOP_VIDEOS_PLAYLIST_VIDEOS_COUNT = 4;

	/**
	 * Video Gallery constructor.
	 */
	public function __construct() {

		parent::__construct(
			'video_featured',
			__( 'PMC Video Featured', 'pmc-top-videos-v2' ), // @TODO: Here and other places - need to change text domain to 'pmc-top-videos' (using 'pmc-top-videos-v2' since pipeline was failing).
			[
				'classname'   => 'video-gallery-featured-widget',
				'description' => __( 'A list of videos from a playlist.', 'pmc-top-videos-v2' ),
			]
		);
	}

	/**
	 * From Templatize Trait.
	 */
	public function widget( $args, $data ) {

		$playlist_videos_count = apply_filters( 'pmc_top_videos_playlist_videos_count', self::PMC_TOP_VIDEOS_PLAYLIST_VIDEOS_COUNT );

		// If wrong number is passed in the filter.
		if ( 0 >= intval( $playlist_videos_count ) ) {
			$playlist_videos_count = self::PMC_TOP_VIDEOS_PLAYLIST_VIDEOS_COUNT;
		}

		$term_slug = ( ! empty( $data['category'] ) ) ? $data['category'] : '';

		if ( empty( $term_slug ) ) {
			return;
		}

		$term_taxonomy = 'vcategory';

		$term_obj = get_term_by( 'slug', $term_slug, $term_taxonomy );

		if ( ! is_a( $term_obj, '\WP_Term' ) ) {
			return;
		}

		$term_link = get_term_link( $term_obj );

		$playlist_title = ( ! empty( $data['title'] ) ) ? $data['title'] : ucfirst( $term_obj->name );
		$playlist_link  = ( ! is_wp_error( $term_link ) ) ? $term_link : '';

		$video_posts = $this->get_playlist_videos( $term_obj, $playlist_videos_count );

		$array_length = count( $video_posts );

		if ( $playlist_videos_count > $array_length ) {
			$video_posts = $this->_backfill_playlist_videos( $video_posts );
		}

		foreach ( $video_posts ?? [] as $video_post ) {

			$image_id  = get_post_thumbnail_id( $video_post->ID );
			$image_src = wp_get_attachment_image_url( $image_id, 'landscape-large' );
			$image_alt = \PMC::get_attachment_image_alt_text( $image_id );

			$video_cards[] = [
				'card_permalink_url'     => get_permalink( $video_post ),
				'card_permalink_classes' => 'pmc-top-videos-list-item-link',
				'video_title_text'       => get_the_title( $video_post ),
				'image_alt_attr'         => $image_alt ?? '',
				'image_url'              => $image_src ?? '',
			];
		}

		$playlist = [
			'playlist_title' => $playlist_title,
			'playlist_link'  => $playlist_link,
			'video_cards'    => $video_cards,
		];

		\PMC::render_template( __DIR__ . '/templates/playlist-module.php', $playlist, true );

	}

	/**
	 * Returns the videos from a vcategory term
	 * This is the cached version & should be the one used.
	 *
	 * @param  WP_Term  $term Term object.
	 * @param  int $count Number of posts to get.
	 * @return array PMC Top Videos objects if posts found else an empty array
	 */
	public function get_playlist_videos( $term, $count = 4 ) {

		if ( ! is_object( $term ) || empty( $term->taxonomy ) || empty( $term->term_id ) ) {
			return [];
		}

		$cache_key = sprintf( 'pmc_top_videos_get_playlist_videos-%s-%s-%d', $term->taxonomy, $term->term_id, $count );

		$cache = new \PMC_Cache( $cache_key );

		$result = $cache->expires_in( 300 ) // 5 minutes
				->updates_with( [ $this, 'get_uncached_playlist_videos' ], [ $term, $count ] )
				->get();

		return ( ! empty( $result ) && is_array( $result ) ) ? $result : [];

	}

	/**
	 * Returns the videos from a vcategory term
	 * This is the uncached version and should not be used directly.
	 *
	 * @see get_playlist_videos()
	 *
	 * @param  WP_Term  $term Term object.
	 * @param  int $count Number of posts to get.
	 * @return array PMC Top Videos objects if posts found else an empty array
	 */
	public function get_uncached_playlist_videos( $term, $count = 4 ) {

		if ( ! is_object( $term ) || empty( $term->taxonomy ) || empty( $term->term_id ) ) {
			return [];
		}

		if ( $count < 1 ) {
			return [];
		}

		$query_args = [
			'post_type'           => 'pmc_top_video',
			'post_status'         => 'publish',
			'tax_query'           => [ // WPCS: slow query ok.
				[
					'taxonomy' => $term->taxonomy,
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				],
			],
			'no_found_rows'       => false,
			'ignore_sticky_posts' => true,
			'include_children'    => false,
			'posts_per_page'      => $count,
			'date_query'          => [
				'after' => '360 day ago',
			],
			'es'                  => true,
		];

		$query = new \WP_Query(
			$query_args
		);

		$video_posts = $query->posts;

		return $video_posts;

	}

	/**
	 * Define the fields that should appear in the widget.
	 *
	 * @return array Fieldmanager fields.
	 */
	protected function fieldmanager_children() {

		return [
			'title'    => new \Fieldmanager_TextField( __( 'Title', 'pmc-top-videos-v2' ) ),
			'category' => new \Fieldmanager_Select(
				[
					'label'   => __( 'Playlist', 'pmc-top-videos-v2' ),
					'options' => $this->_get_categories(),
				]
			),
		];
	}

	/**
	 * Fetch a list of categories to select.
	 *
	 * @return array
	 */
	protected function _get_categories() {

		$categories = [];

		$terms = get_terms(
			[
				'taxonomy' => 'vcategory',
			]
		);

		// Only fetch parent terms.
		foreach ( $terms as $term ) {

			if ( 0 === $term->parent ) {
				$categories[ $term->slug ] = $term->name;
			}
		}

		return $categories;
	}

	/**
	 * Backfill playlist videos with videos not specific to any playlist,
	 */
	private function _backfill_playlist_videos( $video_posts ) {

		// Get all IDs into an array.
		$post_ids = [];
		if ( is_array( $video_posts ) ) {
			$post_ids = array_map(
				function( $item ) {
					return $item->ID;
				},
				(array) $video_posts
			);
		}
		$query_args_2 = [
			'post_type'           => 'pmc_top_video',
			'post_status'         => 'publish',
			'no_found_rows'       => false,
			'ignore_sticky_posts' => true,
			'include_children'    => false,
			'posts_per_page'      => self::PMC_TOP_VIDEOS_PLAYLIST_VIDEOS_COUNT,
			'date_query'          => [
				'after' => '360 day ago',
			],
			'es'                  => true,
		];
		$query2       = new \WP_Query(
			$query_args_2
		);
		$video_posts2 = $query2->posts;

		if ( is_array( $video_posts2 ) && is_array( $post_ids ) ) {
			// Filter duplicate objects.
			$unique_items = array_filter(
				$video_posts2,
				function( $item ) use ( $post_ids ) {
					if ( in_array( $item->ID, (array) $post_ids, true ) ) {
						return false;
					}
					return true;
				}
			);
			$video_posts  = array_merge( $video_posts, $unique_items );

			// Get only first 4 items.
			$video_posts = array_slice( $video_posts, 0, 4 );
		}

		return $video_posts;
	}
}
