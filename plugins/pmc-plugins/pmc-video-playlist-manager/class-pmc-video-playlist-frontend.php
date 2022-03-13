<?php
/**
 *
 * Class PMC_Video_Playlist_Frontend
 *
 * @since 2018-03-14 Jignesh Nakrani READS-1104, READS-1142
 *
 * @package pmc-plugins
 */

namespace PMC\PMC_Video_Playlist;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Video_Playlist_Frontend {

	use Singleton;

	/**
	 * @var object
	 */
	protected $_admin;

	/**
	 * @var array Stores the article(Frontend posts to inject video module) postType slug
	 */
	protected $_article_posttype = array( 'post' );

	/**
	 * __construct method.
	 */
	protected function __construct() {

		$this->_admin = Admin::get_instance();

		if ( ! is_admin() ) {
			add_action( 'init', [ $this, 'setup_frontend' ] );
		}

	}


	/**
	 * @return array returns _article_posttype value
	 */
	public function get_article_posttype() {
		return ( is_array( $this->_article_posttype ) ) ? $this->_article_posttype : array( 'post' );
	}

	/**
	 * Hooks for frontend video playlist module
	 */
	public function setup_frontend() {

		/**
		 * Filter: pmc_video_playlist_manager_article_posttype to override 'article post type'
		 * where Video module suppose to render
		 */
		$this->_article_posttype = apply_filters( 'pmc_video_playlist_manager_article_posttype', $this->_article_posttype );

		add_action( 'wp_enqueue_scripts', [ $this, 'wp_frontend_enqueue_scripts' ] );
	}

	/**
	 * enqueues scripts and styles for frontend Video Playlist module
	 *
	 * @return bool
	 */
	public function wp_frontend_enqueue_scripts() {

		if ( ! is_singular( $this->get_article_posttype() ) ) {
			return false;
		}

		$js_ext = ( \PMC::is_production() ) ? '.min.js' : '.js';

		wp_enqueue_style( 'pmc-video-manager-style', sprintf( '%sassets/css/pmc-video-playlist-manager.min.css', PMC_VIDEO_PLAYLIST_MANAGER_URL ), array(), PMC_VIDEO_PLAYLIST_MANAGER_ROOT );

		wp_enqueue_script( 'pmc-video-player-manager', sprintf( '%sassets/js/video-playlist%s', PMC_VIDEO_PLAYLIST_MANAGER_URL, $js_ext ), array( 'jquery' ), PMC_VIDEO_PLAYLIST_MANAGER_VERSION );
		wp_enqueue_script( 'pmc-video-carousel-manager', sprintf( '%sassets/js/playlist%s', PMC_VIDEO_PLAYLIST_MANAGER_URL, $js_ext ), array(), PMC_VIDEO_PLAYLIST_MANAGER_VERSION );

	}

	/**
	 * Fetch the most suitable Video Playlist Module for current post
	 *
	 * @return \WP_Post|bool if PVM available than returns WP_Post object or else return false.
	 */
	public function get_the_pvm() {

		$flag = false;

		if ( ! is_singular( $this->get_article_posttype() ) ) {
			return $flag;
		}

		$paged          = 0;
		$total          = 0;
		$renderable     = false;
		$posts_per_page = Admin::POSTS_PER_PAGE;
		$post_id        = get_the_ID();

		$args = array(
			'posts_per_page' => $posts_per_page,
			'post_type'      => Admin::POST_TYPE,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'post_date'  => 'DESC',
			),
		);

		foreach ( $this->_admin->get_targeted_taxonomies() as $taxonomy ) {

			$tags = get_the_terms( $post_id, $taxonomy );

			if ( ! empty( $tags ) && ! is_wp_error( $tags ) && is_array( $tags ) ) {

				$flag  = true;
				$terms = wp_list_pluck( $tags, 'term_id' );

				$args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $terms,
				);

				$args['tax_query']['relation'] = 'OR';
			}
		}

		if ( $flag ) {

			do {
				$args['paged'] = ++$paged;

				$video_modules = $this->_admin->get_video_playlist_manager_posts( $args, true );

				if ( empty( $video_modules ) || ! is_array( $video_modules ) ) {
					break;
				}

				$post_count = count( $video_modules );
				$total      = $total + $post_count;

				foreach ( $video_modules as $video_module ) {

					$status = $this->is_pvm_renderable( $video_module );

					if ( true === $status ) {
						$renderable = $video_module;
						break 2;
					}
				}
			} while ( $posts_per_page === $post_count || $total < 100 );
		}
		return $renderable;
	}

	/**
	 * Checks if given video module is match the targeted page rules and timeline frame for given post.
	 *
	 * @param $video_module \wp_post Video module post
	 *
	 * @return boolean True if given video module is renderable for this post otherwise false
	 */
	public function is_pvm_renderable( $video_module ) {

		$start = ( ! empty( $video_module->post_content['start'] ) ) ? $video_module->post_content['start'] : '';
		$end   = ( ! empty( $video_module->post_content['end'] ) ) ? $video_module->post_content['end'] : '';

		if ( ! empty( $start ) && ! empty( $end ) ) {
			if ( ! ( date( 'U', strtotime( $start ) ) < date( 'U' ) && date( 'U', strtotime( $end ) ) > date( 'U' ) ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Fetch the most suitable Video Playlist Module for current post,
	 * According to config in a Video Playlist module retrives videos and appropriate details
	 *
	 * @return array|bool If PVM is available for current post than returns Videos details array for page template or false.
	 */
	public function get_video_carousel() {

		$template_vars  = false;
		$video_module   = $this->get_the_pvm();
		$carousel_posts = array();

		if ( $video_module ) {

			$playlist = empty( $video_module->post_content['playlist'] ) ? '' : $video_module->post_content['playlist'];
			$featured = empty( $video_module->post_content['featured-video'] ) ? '' : $video_module->post_content['featured-video'];
			$count    = empty( $video_module->post_content['video-count'] ) ? 5 : $video_module->post_content['video-count'];

			if ( ! empty( $playlist ) ) {

				$video_posts = $this->_admin->get_playlist_videos( $playlist, $count, $featured );
				$term        = get_term_by( 'name', $playlist, $this->_admin->get_playlist_taxonomy() );

				$playlist_url = ( false !== $term ) ? get_term_link( $term ) : '#';

				if ( is_wp_error( $playlist_url ) ) {
					$playlist_url = '#';
				}

				foreach ( $video_posts->posts as $post ) {

					$carousel_item = $post;

					if ( ! empty( $carousel_item ) ) {

						if ( has_post_thumbnail( $post ) ) {
							$thumb_id = get_post_thumbnail_id( $post );
							$size     = apply_filters( 'pmc_video_playlist_manager_video_thumbnail_size', 'large' );
							$img_src  = wp_get_attachment_image_src( $thumb_id, $size );
							if ( isset( $img_src[0] ) ) {
								$carousel_item->image = $img_src[0];
							}
							$carousel_item->image_alt = \PMC::get_attachment_image_alt_text( $thumb_id, $post );
						}

						$key          = apply_filters( 'pmc_video_playlist_manager_video_meta_key', '_pmc_featured_video_override_data' );
						$video_source = get_post_meta( $post->ID, $key, true );
						$video_source = apply_filters( 'pmc_video_playlist_manager_video_source', $video_source );

						$carousel_item->video_source = $video_source;
						$carousel_item->url          = $post->permalink;

						$carousel_posts[] = $carousel_item;
					}
				}

				if ( ! empty( $video_posts->posts ) ) {
					$template_vars = array(
						'module_title'   => $video_module->post_title,
						'featured_video' => $featured,
						'count'          => $count,
						'videos'         => $carousel_posts,
						'playlist_url'   => $playlist_url,
					);
				}
			}
		}

		return $template_vars;
	}

	/**
	 * Renders the Related Video playlist module on single post
	 */
	public function pmc_get_video_playlist_module() {

		$template_vars = $this->get_video_carousel();

		if ( empty( $template_vars ) ) {
			return '';
		}

		$template = apply_filters( 'pmc_video_playlist_manager_carousel_template', __DIR__ . '/templates/video-playlist-carousel.php' );

		\PMC::render_template( $template, $template_vars, true );

	}

}

