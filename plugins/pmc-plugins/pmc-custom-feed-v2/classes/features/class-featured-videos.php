<?php
namespace PMC\Custom_Feed\Features;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class containing features relating to featured videos.
 * Responsible for appending featured videos in feed-templates.
 *
 * IMPORTANT: Only supports JWPlayer and YouTube vdeos via \PMC_Featured_Video_Override.
 */
class Featured_Videos {

	use Singleton;

	/**
	 * Class initialization routine.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Adds hooks for feed customizations.
	 */
	private function _setup_hooks() {

		// Include featured videos in MSN and Yahoo feed. Hooking on early so it can run before other hooks.
		add_filter( 'pmc_custom_feed_content', array( $this, 'include_featured_video_in_content' ), 9, 4 );
	}

	/**
	 * Include featured video in post content for MSN and Yahoo feeds SADE-507.
	 *
	 * @param string  $content      The content being rendered in the feed item.
	 * @param string  $feed         The feed being accessed.
	 * @param WP_Post $post         Current $post being displayed in the feed.
	 * @param array   $feed_options The current feed's options.
	 *
	 * @return string
	 */
	public function include_featured_video_in_content( $content, $feed, $post, $feed_options ) {

		if (
			( empty( $feed_options['include-featured-video-in-content'] ) && empty( $feed_options['include-featured-video-after-para-1'] ) )
			|| empty( $post )
		) {
			return $content;
		}

		// Get featured video from post meta.
		$featured_video = $this->get_featured_video_meta( $post->ID );

		// Bail out if there's no featured video.
		if ( empty( $featured_video ) ) {
			return $content;
		}

		// If it's MSN feed and the featured video is jwplayer then return <video></video> tag.
		if ( $this->is_msn_feed() && 'jwplayer' === $this->get_featured_video_type( $featured_video ) ) {

			$format = 'video';
		} else {

			// It's Yahoo feed, return iframe tag.
			$format = 'iframe';

			// MS Feeds remove some html tags inlucding iframe, need to whitelist iframe using the filer.
			add_filter(
				'pmc_custom_feed_ms_html_blocklist',
				function( $blocklist ) {
					return array_diff( $blocklist, [ 'iframe' ] );
				}
			);
		}

		$featured_video_node = $this->get_featured_video_node( $post, $format );

		if ( ! empty( $feed_options['include-featured-video-after-para-1'] ) ) {

			$closing_p  = '</p>';
			$move_after = 1;

			$paragraphs = explode( $closing_p, $content );

			if ( is_array( $paragraphs ) && count( $paragraphs ) >= $move_after ) {

				$new_content = implode( $closing_p, array_slice( $paragraphs, 0, $move_after ) ) . $closing_p;
				$paragraphs  = array_slice( $paragraphs, $move_after );

				// Append the video content.
				$new_content .= $featured_video_node;
				$new_content .= implode( $closing_p, $paragraphs );
				$content      = $new_content;

			}

		} else {
			$content = $featured_video_node . $content;
		}

		return $content;
	}

	// Helper function to determine if the feed is for MSN.
	public function is_msn_feed() {

		$feed_options = \PMC_Custom_Feed::get_instance()->get_feed_config();

		if ( ! empty( $feed_options['msfeed'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns featured video of the post if any.
	 *
	 * @param string $post   Post ID or Object
	 *
	 * @param string $format The node to use; Accepts 'iframe' ( i.e. <iframe></iframe> ), 'media' ( i.e. <media:content><media:content> ) and 'video' ( i.e. <video></video> ); defaults to 'media'.
	 *
	 * @return string
	 */
	public function get_featured_video_node( $post = 0, $format = 'media' ) {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return;
		}

		$featured_video = $this->get_featured_video( $post, $format );

		if ( empty( $featured_video ) ) {
			return;
		}

		$attachment_id = get_post_thumbnail_id( $post->ID );
		$thumbnail     = $this->get_media_url( $attachment_id );

		if ( is_string( $featured_video ) ) {
			$featured_video = array(
				'id'        => $post->ID,
				'title'     => $post->post_title,
				'thumbnail' => $thumbnail,
				'duration'  => '',
				'link'      => $featured_video,
				'type'      => 'application/x-shockwave-flash',
				'height'    => '100%',
				'width'     => '100%',
			);
		} elseif ( is_array( $featured_video ) ) {
			$featured_video['thumbnail'] = ( ! empty( $featured_video['thumbnail'] ) ) ? $featured_video['thumbnail'] : $thumbnail;
		} else {
			// This case will never happen unless something is really wrong.
			// Code added for completeness, no proper way to test the code.
			return; // @codeCoverageIgnore
		}

		if ( 'iframe' === $format ) {

			return sprintf(
				'<iframe src="%1$s" width="%2$s" height="%3$s" frameborder="0" allowfullscreen="true"></iframe>',
				esc_url_raw( $featured_video['link'] ),
				esc_attr( $featured_video['width'] ),
				esc_attr( $featured_video['height'] )
			);

		} elseif ( 'video' === $format ) {

			return sprintf(
				'<video id="%1$s" title="%2$s" poster="%3$s"><source src="%4$s" type="%5$s"></source></video>',
				esc_attr( $featured_video['id'] ),
				esc_attr( $featured_video['title'] ),
				esc_attr( $featured_video['thumbnail'] ),
				esc_url_raw( $featured_video['link'] ),
				esc_attr( $featured_video['type'] )
			);

		} else {

			// Media tag.
			return sprintf(
				'<media:content url="%1$s" type="%2$s" medium="video"><media:title>%3$s</media:title><media:thumbnail url="%4$s"/></media:content>',
				\PMC_Custom_Feed_Helper::esc_xml( $featured_video['link'] ),
				\PMC_Custom_Feed_Helper::esc_xml( $featured_video['type'] ),
				\PMC_Custom_Feed_Helper::esc_xml( $featured_video['title'] ),
				\PMC_Custom_Feed_Helper::esc_xml( $featured_video['thumbnail'] )
			); // WPCS: XSS ok, all contents are properly xml encoded
		}
	}

	/**
	 * Helper function to determine type of video, either youtube or jwplayer or any other.
	 *
	 * @param string $featured_video Featured video meta
	 *
	 * @return string Returns 'youtube' if it's a youtube video and 'jwplayer' if it's a jwplayer video otherwise returns 'other'.
	 */
	public function get_featured_video_type( $featured_video ) {

		// Possible youtube domains.
		$youtube_domain = array(
			'youtu.be',
			'www.youtube.com',
			'youtube.com',
		);

		// When its a youtube video.
		if ( wpcom_vip_is_valid_domain( $featured_video, $youtube_domain ) ) {
			return 'youtube';
		} elseif ( false !== strpos( $featured_video, 'jwplatform' ) || false !== strpos( $featured_video, 'jwplayer' ) ) {
			return 'jwplayer';
		}

		return 'other';
	}

	/**
	 * Get featured video of the post if any. Only supports youtube and jwplayer videos.
	 *
	 * @param string $post Post
	 *
	 * @param string $format Which format the video will be used in. Accepts 'iframe' ( i.e. <iframe></iframe> ), 'media' ( i.e. <media:content><media:content> ); defaults to 'media'.
	 *
	 * @return string|array Returns video url if it's a youtube video or an array containing video data if it's jwplayer video.
	 */
	public function get_featured_video( $post = 0, $format = 'media' ) {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return '';
		}

		// Get featured video from post meta.
		$video = $this->get_featured_video_meta( $post->ID );

		if ( empty( $video ) ) {
			return '';
		}

		$video_type = $this->get_featured_video_type( $video );

		// When its a youtube video.
		if ( 'youtube' === $video_type ) {

			$video = str_replace( 'www.', '', $video );

			$replacement = 'https://www.youtube.com/v/$1?version=3';

			// If video is for iframe then return the embed url.
			if ( 'iframe' === $format ) {
				$replacement = 'https://www.youtube.com/embed/$1';
			}

			// Unify the youtube url.
			if ( strpos( $video, 'youtu.be' ) ) {
				$video = preg_replace( '~^https?://youtu\.be/([a-z-\d_]+)$~i', $replacement, $video );
			} elseif ( strpos( $video, 'youtube.com/watch' ) ) {
				$video = preg_replace( '~^https?://youtube\.com\/watch\?v=([a-z-\d_]+)$~i', $replacement, $video );
			}

			return $video;

		} elseif ( 'jwplayer' === $video_type ) {

			// When its jwplayer video.

			$pattern = get_shortcode_regex();
			preg_match_all( '/' . $pattern . '/s', $video, $matches );

			if ( ! empty( $matches[3] ) && ! empty( $matches[3][0] ) ) {

				$jw_player_id = trim( $matches[3][0] );
				$video        = $this->get_jwplayer_video( $jw_player_id, array( '320px', '480px', '720px' ) );

				if ( ! $video || ! is_array( $video ) ) {
					return '';
				}

				// If video will be used in iframe then return player url for the video.
				if ( 'iframe' === $format ) {
					$video['link'] = sprintf( 'https://%1$s/players/%2$s.html', JWPLAYER_CONTENT_MASK, $video['id'] );
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
		 * Filters featured video used for feeds.
		 *
		 * @param string $video Video data, can be a oembed url or shortcode.
		 */
		return apply_filters( 'pmc_custom_feed_feature_featured_video', $video );
	}

	/**
	 * Get jwplayer video link using jwplayer API.
	 * https://developer.jwplayer.com/jw-platform/docs/delivery-api-reference/#/Media/get_v2_media__media_id
	 *
	 * @param string $video_id    jwplayer unique 8 character video id.
	 *
	 * @param array  $resolutions Resolutions to fetch, possible values: '320px','480px', '720px', '1280px', '1920px'; fetches all resolutions if left empty.
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
	 *   'height'      => '1080',
	 *   'width'       => '1920',
	 * )
	 *
	 */
	public function get_jwplayer_video( $video_id, $resolutions = array() ) {

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
		$api_url = apply_filters( 'pmc_custon_feed_feature_featured_videos_jwplayer__api_url', 'https://cdn.jwplayer.com/v2/media/%1$s?format=json&sources=%2$s' );

		$response = vip_safe_wp_remote_get( sprintf( $api_url, $video_id, implode( ',', (array) $resolutions ) ) );

		if ( is_wp_error( $response ) || empty( wp_remote_retrieve_body( $response ) ) ) {
			return false;
		}

		$response = json_decode( wp_remote_retrieve_body( $response ) );

		if ( empty( $response->playlist ) || empty( $response->playlist[0]->sources ) ) {
			return false;
		}

		$playlist              = $response->playlist[0];
		$sources               = $playlist->sources;
		$retrieved_resolutions = wp_list_pluck( $sources, 'width' );

		// Get the index of the highest resolution video.
		$video_index = array_keys( (array) $retrieved_resolutions, max( $retrieved_resolutions ), true )[0];

		$title       = ( ! empty( $response->title ) ) ? $response->title : '';
		$image       = ( ! empty( $playlist->image ) ) ? $playlist->image : '';
		$duration    = ( ! empty( $playlist->duration ) ) ? $playlist->duration : '';
		$description = ( ! empty( $playlist->description ) ) ? $playlist->description : $title;
		$link        = ( ! empty( $sources[ $video_index ]->file ) ) ? $sources[ $video_index ]->file : '';
		$type        = ( ! empty( $sources[ $video_index ]->type ) ) ? $sources[ $video_index ]->type : '';
		$height      = ( ! empty( $sources[ $video_index ]->height ) ) ? $sources[ $video_index ]->height : '';
		$width       = ( ! empty( $sources[ $video_index ]->width ) ) ? $sources[ $video_index ]->width : '';

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
			'height'      => $height,
			'width'       => $width,
		];

		return $video;
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

}

// Initialize class.
Featured_Videos::get_instance();
