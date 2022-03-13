<?php
/**
 * This class adds feed compatibility for List ( pmc-lists plugin ) posts.
 */

namespace PMC\Custom_Feed;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Gallery\Lists as Gallery_Lists;

class Lists {

	use Singleton;

	/**
	 * Link to original article text.
	 *
	 * @var string
	 */
	private $_link_to_article_text = 'View the full Article';

	/**
	 * List numbering
	 * Valid values are = { 'asc', 'desc', 'none' }
	 *
	 * @var string
	 */
	private $_list_numbering = 'none';

	/**
	 * List item current index;
	 *
	 * @var int
	 */
	private $_index;

	/**
	 * Use to update current index by one or minus one.
	 * Valid values are = { -1, 0, 1 }
	 *
	 * @var int
	 */
	private $_order;

	/**
	 * True if parent post.
	 *
	 * @var bool
	 */
	private $_is_parent_post;

	private $_feed_options = [];

	/**
	 * Cache group name
	 */
	const CACHE_GROUP = 'pmc_custom_feed_pmc_lists';

	/**
	 * List of registered formats for rendering the list.
	 *
	 * Note: Need to register each new rendering-format here in order for cache to be invalidated when the list / list-item is updated.
	 */
	const REGISTERED_FORMATS = [
		'article',
		'slideshow',
	];

	/**
	 * Class initialization routine.
	 *
	 * Setup data for to output feed.
	 * Setup relationship between two post types.
	 *
	 */
	protected function __construct() {
		add_action( 'pmc_custom_feed_start', [ $this, 'action_pmc_custom_feed_start' ], 10, 3 );
		if ( is_admin() ) {
			add_action( 'admin_init', [ $this, 'action_admin_init' ] );
		}
	}

	public function action_admin_init() {
		add_action( 'save_post_pmc_list', [ $this, 'invalidate_cache' ], 10, 3 );
		add_action( 'save_post_pmc_list_item', [ $this, 'invalidate_cache' ], 10, 3 );
	}

	public function action_pmc_custom_feed_start( $feed = false, $feed_options = false, $template = '' ) {
		if ( 'feed-lists.php' === $template ) {
			$this->_feed_options = $feed_options;
		}
	}

	/**
	 * WP Action hook to trigger cache invalidation
	 */
	public function invalidate_cache( $post_id, $post, $update ) {

		if ( ! $update || empty( $post_id ) || empty( $post ) ) {
			return false;
		}

		// If the post being updated is list item then clear the cache of the list to which the list item belongs to.
		// This is because we are caching based on list not list item.
		if ( \PMC\Lists\Lists::LIST_ITEM_POST_TYPE === $post->post_type ) {

			// Get the list to which this list item belongs to.
			$list_terms = get_the_terms( $post_id, \PMC\Lists\Lists::LIST_RELATION_TAXONOMY );

			// Return if no list found for the list item.
			if ( ! $list_terms || empty( $list_terms ) || is_wp_error( $list_terms ) ) {
				return false;
			}

			$post_id = $list_terms[0]->name;
		}

		// Invalidate the cache for all registered formats.
		foreach ( self::REGISTERED_FORMATS as $format ) {
			$this->get_pmc_cache( intval( $post_id ), $format )->invalidate();
		}

		return;
	}

	/**
	 * Helper function to return the pmc cache object to be use for caching
	 */
	public function get_pmc_cache( int $post_id, string $format ) {
		return new \PMC_Cache( $format . $post_id, self::CACHE_GROUP );
	}

	/**
	 * Get all list items for given list.
	 *
	 * @param string $term List item id.
	 *
	 * @return array
	 */
	public function get_lists_items( $term ) {

		$items_query_args = [
			'post_status'    => 'publish',
			'post_type'      => \PMC\Lists\Lists::LIST_ITEM_POST_TYPE,
			'posts_per_page' => 100,
			'orderby'        => 'menu_order title',
			'order'          => 'asc',
			'tax_query'      => [ // WPCS: slow query ok.
				[
					'taxonomy' => \PMC\Lists\Lists::LIST_RELATION_TAXONOMY,
					'field'    => 'slug',
					'terms'    => $term,
				],
			],
		];

		$results = [];

		$query = new \WP_Query( $items_query_args );

		if ( ! is_wp_error( $query ) && $query->have_posts() ) {
			$results = $query->posts;
		}

		return $results;
	}

	/**
	 * Render all list items inline as html for given post.
	 *
	 * @param int|WP_Post $post Post object or id.
	 */
	public function get_html( $post, $format = 'article' ) {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return;
		}

		$format = ( ! empty( $format ) ) ? $format : 'article';

		// Bail out if the format is not registered.
		if ( ! in_array( $format, (array) self::REGISTERED_FORMATS, true ) ) {
			return;
		}

		$pmc_cache = $this->get_pmc_cache( $post->ID, $format );

		// Caching the list for a longer time as list content is not updated frequently.
		// Implement caching on entire xml output
		return $pmc_cache->expires_in( HOUR_IN_SECONDS * 12 )
			->updates_with(
				function( $post, $format ) {

					ob_start(); // start capturing the xml output

					if ( 'article' === $format ) {

						$this->render_html_list_uncached( $post );

					} elseif ( 'slideshow' === $format ) {

						$this->render_list_slideshow_uncached( $post );
					}

					return ob_get_clean(); // return the captured xml output content to be cached
				},
				[ $post, $format ]
			)
			->get();

	}

	/**
	 * Get inline list items render. Assumes that list items will be rendered inside cdata element.
	 *
	 * @param int|WP_Post $post Post object or id.
	 */
	public function render_html_list_uncached( $post ) {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return '';
		}

		$this->_is_parent_post = true;

		// Render media and content for the List (parent) post.
		$this->render_media_node( $post, 'html' );

		// @see the_content
		$content = apply_filters( 'the_content', \PMC::strip_shortcodes( $post->post_content ) );
		$content = str_replace( ']]>', ']]&gt;', $content );
		echo wp_kses_post( $content ) . '<br />';

		$this->_is_parent_post = false;

		// Get list items of the current list.
		$list_items = $this->get_lists_items( $post->ID );

		$this->setup_list_numbering( $post->ID );
		$this->setup_index( $list_items );

		// Process each list items and render title, media and content.
		foreach ( (array) $list_items as $item ) {

			echo '<h2>' . esc_html( $this->get_list_item_title( $item ) ) . '</h2><br />';
			$this->render_media_node( $item, 'html' );
			echo '<br />';

			// @see the_content
			$content = apply_filters( 'the_content', \PMC::strip_shortcodes( $item->post_content ) );
			$content = str_replace( ']]>', ']]&gt;', $content );

			if ( ! empty( $content ) ) {
				echo wp_kses_post( $content );
			}

			$this->_index = $this->_index + $this->_order;

		}

		$this->reset_list_numbering();

	}

	/**
	 * Get inline list items rendered in slideshow format.
	 * @see https://partnerhub.msn.com/docs/example/vcurrent/example-rss-slideshow/AAsCx
	 *
	 * @param int|WP_Post $post Post object or id.
	 */
	public function render_list_slideshow_uncached( $post ) {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return '';
		}

		$this->_is_parent_post = true;

		// Render media and content for the List (parent) post.
		$this->render_media_node( $post, 'media' );

		$this->_is_parent_post = false;

		// Get list items of the current list.
		$list_items = $this->get_lists_items( $post->ID );

		/**
		 * Let's sort the list items according to the user-specified order saved in post_meta.
		 * @see https://jira.pmcdev.io/browse/PASE-785
		 */
		$list_items = Gallery_Lists::sort_list_items( $post->ID, $list_items );

		$this->setup_list_numbering( $post->ID );
		$this->setup_index( $list_items );

		// Disable featured videos.
		add_filter( 'pmc_custom_feed_lists_featured_video', '__return_false', 11 );

		// Process each list items and render title, media and content.
		foreach ( (array) $list_items as $item ) {

			$this->render_media_node( $item, 'media', $post );
			$this->_index = $this->_index + $this->_order;
		}

		// Remove the filter we added for disabling the featured videos.
		remove_filter( 'pmc_custom_feed_lists_featured_video', '__return_false', 11 );

		$this->reset_list_numbering();

	}

	/**
	 * Setup current index for numbering.
	 *
	 * @param array $lis_items List items.
	 */
	public function setup_index( $lis_items ) {

		$this->_index = 1;
		$this->_order = 1;

		if ( ! is_array( $lis_items ) ) {
			return;
		}

		if ( 'desc' === $this->_list_numbering ) {
			$this->_index = count( $lis_items );
			$this->_order = -1;
		}
	}

	/**
	 * Setup list numbering from meta source.
	 *
	 * @param int $post_id Post id.
	 */
	public function setup_list_numbering( $post_id ) {

		if ( ! intval( $post_id ) ) {
			return;
		}

		$numbering = get_post_meta( $post_id, \PMC\Lists\Lists::NUMBERING_OPT_KEY, true );

		$val = 'none';

		switch ( $numbering ) {

			case 'asc':
			case 'desc':
				$val = $numbering;
				break;
			default:
				break;
		}

		$this->_list_numbering = $val;
	}

	/**
	 * Setup list numbering from meta source.
	 */
	private function reset_list_numbering() {
		$this->_list_numbering = 'none';
	}

	/**
	 * Render media node for given post object as html content
	 *
	 * @param \WP_Post|int $post Post object.
	 * @param \WP_Post|int $main_article Main article object. Default to 0.
	 *
	 * @return void
	 */
	public function render_media_node( $post = 0, $format = 'html', $main_article = 0 ) {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return;
		}

		// Get attachment or featured video if any.
		$attachment_id  = get_post_thumbnail_id( $post->ID );
		$featured_video = $this->get_featured_video_meta( $post->ID );

		// Don't render media content if no thumbnail and featured video.
		if ( empty( $attachment_id ) && empty( $featured_video ) ) {
			return;
		}

		// If we have featured video then render that else featured image.
		if ( ! empty( $featured_video ) ) {
			// Render the featured video node.
			$this->render_featured_video_node( $post, $format );
			return;
		}

		$media_url = $this->get_media_url( $attachment_id );

		if ( 'html' === $format ) {
			// Get media credit and syndication rights.
			$media_credit       = \PMC_Custom_Feed_Helper::get_image_credit( $attachment_id );
			$syndication_rights = ( isset( $this->_feed_options['msn-syndication-rights'] ) && true === $this->_feed_options['msn-syndication-rights'] ) ? '1' : '';

			if ( ! empty( $media_credit ) ) {
				printf(
					'<img src="%1$s" alt="%2$s" data-portal-copyright="%3$s" data-has-syndication-rights="%4$s" width="960px" height="auto">',
					esc_url_raw( $media_url ),
					esc_attr( $this->get_list_item_title( $post ) ),
					esc_attr( $media_credit ),
					esc_attr( $syndication_rights )
				);
			}

		} else {
			$media_credit = \PMC_Custom_Feed_Helper::render_image_credit_tag( $attachment_id, false );

			if ( ! empty( $media_credit ) ) {
				printf(
					'<media:content url="%1$s" type="%2$s" medium="image"><media:title>%3$s</media:title>%4$s</media:content>',
					\PMC_Custom_Feed_Helper::esc_xml( $media_url ),
					\PMC_Custom_Feed_Helper::esc_xml( $this->get_media_mime_type( $media_url ) ),
					\PMC_Custom_Feed_Helper::esc_xml( $this->get_list_item_title( $post ) ),
					$media_credit . $this->get_media_desc_node( $post, 'cdata', $main_article )
				); // WPCS: XSS ok, all contents are xml properly encoded
			}
		}
	}

	/**
	 * Renders featured video of the post if any.
	 *
	 * @param string $post Post
	 *
	 * @return void
	 */
	public function render_featured_video_node( $post = 0, $format = 'html' ) {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return;
		}

		$video = $this->get_featured_video( $post );

		if ( empty( $video ) ) {
			return;
		}

		// Its a JWPlayer video.
		if ( is_array( $video ) && isset( $video['link'] ) && ! empty( $video['link'] ) ) {

			$attachment_id = get_post_thumbnail_id( $post->ID );
			$thumbnail     = ( empty( $video['thumbnail'] ) ) ? $this->get_media_url( $attachment_id ) : $video['thumbnail'];

			if ( 'html' === $format ) {

				printf(
					'<video id="%1$s" title="%2$s" poster="%3$s" data-description="%4$s" ><source src="%5$s" type="%6$s"></source></video>',
					esc_attr( $video['id'] ),
					esc_attr( $video['title'] ),
					esc_attr( $thumbnail ),
					esc_attr( $video['description'] ),
					esc_url_raw( $video['link'] ),
					esc_attr( $video['type'] )
				);

			} else {

				printf(
					'<media:content url="%1$s" duration="%2$s" type="%3$s" medium="video"><media:title>%4$s</media:title><media:description>%5$s</media:description><media:thumbnail url="%6$s"/></media:content>',
					\PMC_Custom_Feed_Helper::esc_xml( $video['link'] ),
					\PMC_Custom_Feed_Helper::esc_xml( $video['duration'] ),
					\PMC_Custom_Feed_Helper::esc_xml( $video['type'] ),
					\PMC_Custom_Feed_Helper::esc_xml( $this->get_list_item_title( $post ) ),
					\PMC_Custom_Feed_Helper::esc_xml_cdata( $video['description'] ),
					\PMC_Custom_Feed_Helper::esc_xml( $thumbnail )
				); // WPCS: XSS ok, all contents are properly xml encoded

			}

		} else {

			// Its a youtube video.
			if ( 'html' === $format ) {

				// Render this inline using html element.
				printf( '<iframe src="%1$s" width="100%%" height="100%%" frameborder="0" allowfullscreen="true"></iframe>', esc_url_raw( $video ) );

			} else {

					printf(
						'<media:content url="%1$s" type="application/x-shockwave-flash" medium="video" expression="full"><media:title>%2$s</media:title>%3$s</media:content>',
						\PMC_Custom_Feed_Helper::esc_xml( $video ),
						\PMC_Custom_Feed_Helper::esc_xml( $this->get_list_item_title( $post ) ),
						$this->get_media_desc_node( $post, 'cdata' )
					); // WPCS: XSS ok, all contents are properly xml encoded

			}
		}
	}

	/**
	 * Get featured video of the post if any.
	 *
	 * @param string $post Post
	 *
	 * @return string|array
	 */
	public function get_featured_video( $post = 0 ) {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return '';
		}

		// Possible youtube domains.
		$youtube_domain = array(
			'youtu.be',
			'www.youtube.com',
			'youtube.com',
		);

		// Get featured video from post meta.
		$video = $this->get_featured_video_meta( $post->ID );

		if ( empty( $video ) ) {
			return '';
		}

		// When its a youtube video.
		if ( wpcom_vip_is_valid_domain( $video, $youtube_domain ) ) {

			$video = str_replace( 'www.', '', $video );

			// Unify the youtube url.
			if ( strpos( $video, 'youtu.be' ) ) {
				$video = preg_replace( '~^https?://youtu\.be/([a-z-\d_]+)$~i', 'https://www.youtube.com/v/$1?version=3', $video );
			} elseif ( strpos( $video, 'youtube.com/watch' ) ) {
				$video = preg_replace( '~^https?://youtube\.com\/watch\?v=([a-z-\d_]+)$~i', 'https://www.youtube.com/v/$1?version=3', $video );
			}

			return $video;

		} elseif ( false !== strpos( $video, 'jwplatform' ) || false !== strpos( $video, 'jwplayer' ) ) {

			// When its jwplayer video.

			$pattern = get_shortcode_regex();
			preg_match_all( '/' . $pattern . '/s', $video, $matches );

			if ( ! empty( $matches[3] ) && ! empty( $matches[3][0] ) ) {

				$jw_player_id = trim( $matches[3][0] );
				$video        = $this->get_jwplayer_video( $jw_player_id );

				if ( ! $video || ! is_array( $video ) ) {
					return '';
				}

				return $video;
			}

		}

		return '';

	}

	/**
	 * Gets the featured video from post meta
	 *
	 * @param int $post Post ID.
	 */
	public function get_featured_video_meta( $post_id ) {

		// Retrieve assigned featured video to the post.
		$featured_video_meta_key = ( class_exists( '\PMC_Featured_Video_Override' ) ) ? \PMC_Featured_Video_Override::META_KEY : '_pmc_featured_video_override_data';
		$video                   = get_post_meta( $post_id, $featured_video_meta_key, true );

		/**
		 * Filters featured video used in pmc_list feeds.
		 *
		 * @param string $video Video data, can be a oembed url or shortcode or iframe
		 */
		return apply_filters( 'pmc_custom_feed_lists_featured_video', $video );
	}

	/**
	 * Get jwplayer video link using jwplayer API.
	 * https://developer.jwplayer.com/jw-platform/docs/delivery-api-reference/#/Media/get_v2_media__media_id
	 *
	 * @param string $video_id jwplayer unique 8 character video id.
	 *
	 * @return array|bool Returns false if something is wrong else an Array containing all the information of video. Returns array of the format:
	 *
	 * array (
	 *   'id'          => 'JOOltkLa,
	 *   'title'       => 'NYC Pride: That's A Wrap',
	 *   'thumbnail'   => 'https://cdn.jwplayer.com/thumbs/JOOltkLa-720.jpg',
	 *   'duration'    => '32',
	 *   'link'        => 'https://cdn.jwplayer.com/videos/JOOltkLa-JU0RFN84.mp4',
	 *   'type'        => 'video/mp4',
	 *   'description' => 'Dummy desc',
	 * )
	 *
	 */
	public function get_jwplayer_video( $video_id ) {

		// $video_id is always at least 8 characters long.
		if ( empty( $video_id ) || 8 > strlen( $video_id ) ) {
			return false;
		}

		// There are instances where video ids might be containing '-' if its a specific variation.
		$video_id = explode( '-', $video_id )[0];

		/**
		 * Get video link and meta from jwplayer's API using video id.
		 * https://developer.jwplayer.com/jw-platform/docs/delivery-api-reference/#/Media/get_v2_media__media_id
		 */
		$api_url = apply_filters( 'pmc_custon_feed_lists_jwplayer__api_url', 'https://cdn.jwplayer.com/v2/media/%1$s?format=json' );

		$response = vip_safe_wp_remote_get( sprintf( $api_url, $video_id ) );

		if ( is_wp_error( $response ) || empty( wp_remote_retrieve_body( $response ) ) ) {
			return false;
		}

		$response = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! isset( $response->playlist ) ) {
			return false;
		}

		$playlist = $response->playlist[0];

		$title       = ( ! empty( $response->title ) ) ? $response->title : '';
		$image       = ( ! empty( $playlist->image ) ) ? $playlist->image : '';
		$duration    = ( ! empty( $playlist->duration ) ) ? $playlist->duration : '';
		$link        = ( ! empty( $playlist->sources[1]->file ) ) ? $playlist->sources[1]->file : '';
		$type        = ( ! empty( $playlist->sources[1]->type ) ) ? $playlist->sources[1]->type : '';
		$description = ( ! empty( $playlist->description ) ) ? $playlist->description : $title;

		if ( empty( $link ) || false === strpos( $link, 'jwplayer' ) ) {
			return false;
		}

		$video = [
			'id'          => $video_id,
			'title'       => $title,
			'thumbnail'   => $image,
			'duration'    => $duration,
			'link'        => $link,
			'type'        => $type,
			'description' => $description,
		];

		return $video;

	}

	/**
	 * Get media:description node for feed.
	 * This method wraps post content inside media:description node.
	 *
	 * @param \WP_Post $post  post object.
	 * @param bool    $format Whether to wrap the output in cdata tag.
	 * @param \WP_Post|int $main_article Main article object. Default to 0.
	 *
	 * @return string
	 */
	public function get_media_desc_node( $post, $format = 'xml', $main_article = 0 ) {

		$censor_curse_words_content = apply_filters( 'pmc_custom_feed_censor_curse_words', \PMC::strip_shortcodes( $post->post_content ) );

		if ( $main_article ) {
			/**
			 * Main article is provided, that means we want to render main post url instead of individual list items.
			 *
			 * @see https://jira.pmcdev.io/browse/PASE-785 => issue #2
			 */
			$post = get_post( $main_article );
		}

		if ( is_a( $post, 'WP_Post' ) && ! empty( $post->post_content ) ) {
			if ( 'cdata' === $format ) {
				return '<media:description>' . \PMC_Custom_Feed_Helper::esc_xml_cdata( $censor_curse_words_content . $this->get_link_to_full_article( $post ) ) . '</media:description>';
			} else {
				return '<media:description>' . \PMC_Custom_Feed_Helper::esc_xml( $censor_curse_words_content . $this->get_link_to_full_article( $post ) ) . '</media:description>';
			}

		}
		return '';

	}

	/**
	 * Get link to full article with text.
	 *
	 * @param WP_Post|int $post Post object.
	 *
	 * @return string Link to original article.
	 */
	public function get_link_to_full_article( $post = 0 ) {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return '';
		}

		return sprintf(
			'<p><a href="%1$s">%2$s</a></p>',
			\PMC_Custom_Feed_Helper::esc_xml( get_the_permalink( $post ) ),
			\PMC_Custom_Feed_Helper::esc_xml( apply_filters( 'pmc_custom_feed_list_link_to_article_text', $this->_link_to_article_text, $post ) )
		);
	}

	/**
	 * Get title for feed, uses post title
	 *
	 * @param int         $attachment_id Attachment id.
	 * @param WP_Post|int $post Post     object.
	 *
	 * @return string
	 */
	public function get_list_item_title( $post ) {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return '';
		}

		$title = get_the_title( $post );

		$title = wp_specialchars_decode( wp_strip_all_tags( $title ) );

		if ( 'none' !== $this->_list_numbering ) {

			$title = sprintf( '%1$s. %2$s', strval( $this->_index ), $title );
		}

		$title = apply_filters( 'pmc_custom_feed_image_title', $title );

		return $title;

	}

	/**
	 * Get media url for given attachment.
	 *
	 * @param int $attachment_id Attachment id.
	 *
	 * @return string
	 */
	public function get_media_url( $attachment_id ) {

		if ( ! intval( $attachment_id ) ) {
			return '';
		}

		$feed_image_size = \PMC_Custom_Feed::get_instance()->get_feed_image_size();

		$image = wp_get_attachment_image_src( $attachment_id, 'full' );

		if ( empty( $image ) ) {
			return '';
		}

		list( $img_url, $width, $height ) = $image;

		if (
			is_array( $feed_image_size ) &&
			isset( $feed_image_size['width'] ) &&
			isset( $feed_image_size['height'] )
		) {
			$width  = $feed_image_size['width'];
			$height = $feed_image_size['height'];
		}

		if ( function_exists( 'jetpack_photon_url' ) ) {
			$img_url = jetpack_photon_url( $img_url, array( 'resize' => array( (int) $width, (int) $height ) ) );
		}

		return $img_url;
	}

	/**
	 * Get mime type of media from given url.
	 *
	 * @param string $url URL of media.
	 *
	 * @return string
	 */
	public function get_media_mime_type( $url ) {

		if ( empty( $url ) ) {
			return '';
		}

		$ext = strtolower( pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );

		switch ( $ext ) {
			case 'gif':
				$mime_type = 'image/gif';
				break;
			case 'png':
				$mime_type = 'image/png';
				break;
			default:
				$mime_type = 'image/jpeg';
				break;
		}

		return $mime_type;
	}

}

Lists::get_instance();

// EOF
