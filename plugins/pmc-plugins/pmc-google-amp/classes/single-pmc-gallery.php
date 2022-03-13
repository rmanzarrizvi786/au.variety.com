<?php

namespace PMC\Google_Amp;

use PMC;
use \PMC\Global_Functions\Traits\Singleton;
use \PMC_Gallery_Defaults;
use \AMP_DOM_Utils;
use \AMP_Img_Sanitizer;
use \PMC_Cache;
use \PMC_Gallery_View;
use \PMC_Cheezcap;

class Single_PMC_Gallery {

	use Singleton;

	/**
	 * @var \PMC\Google_Amp\Single_Post
	 */
	protected $_single_post;

	/**
	 * Key for cache gallery data.
	 */
	const PMC_GALLERY_CACHE_KEY = 'pmc_google_amp_gallery_';


	/**
	 * Key group of cache gallery data.
	 */
	const PMC_GALLERY_CACHE_GROUP_KEY = 'pmc_google_amp_gallery';

	protected function __construct() {

		$this->_single_post = Single_Post::get_instance();

		/**
		 * Actions
		 */
		add_action( 'amp_init', array( $this, 'action_amp_init' ) );
		add_action( 'init', array( $this, 'action_init' ) );
		add_action( 'template_redirect', array( $this, 'action_template_redirect' ) );

		/**
		 * Filter.
		 */
		add_filter( 'pmc_google_amp_ga_event_tracking', array( $this, 'add_ga_event_tracking' ) );
	}

	public function action_amp_init() {
		add_post_type_support( 'pmc-gallery', AMP_QUERY_VAR );
	}

	/**
	 * Conditional method to check if current URL is AMP URL or not
	 *
	 * @since 2017-07-31 Dhaval Parekh CDWE-449
	 *
	 * @return boolean Returns TRUE if current URL is AMP URL else FALSE
	 */
	protected function _is_amp() {

		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return true;
		}

		return false;

	}

	public function action_init() {
		add_rewrite_rule( 'pics/([^/]+)/([^/]+)/amp(/(.*))?/?$', 'index.php?pmc-gallery=$matches[1]&gallery-image=$matches[2]&amp=', 'top' );
		add_rewrite_tag( '%gallery-image%', '([^/]+)' );
	}

	public function action_template_redirect() {
		if ( ! is_singular( 'pmc-gallery' ) ) {
			return;
		}

		add_filter( 'pmc_post_amp_title', '__return_empty_string' ); // PMCVIP-2581. Do not render title
		add_filter( 'amp_post_template_meta_parts', '__return_empty_array' ); // PMCVIP-2581. Do not render author, date
		add_filter( 'pmc_post_amp_content', array( $this, 'filter_pmc_post_amp_content' ) );
		add_filter( 'pmc_post_amp_content', array( $this, 'add_gallery_thumbnails' ) );
		add_action( 'amp_post_template_css', array( $this, 'action_amp_post_template_css' ) );
		add_action( 'amp_post_template_head', array( $this, 'action_amp_post_template_head' ) );
	}

	/**
	 * To get status of "Enable Gallery thumbnail"  theme options.
	 *
	 * @return bool return true if "Enable Gallery thumbnail" is set  to "Yes" otherwise false.
	 */
	protected function _is_gallery_thumbnail_enabled() {
		return ( 'yes' === strtolower( PMC_Cheezcap::get_instance()->get_option( Single_Post::AMP_GALLERY_THUMBNAIL_STATUS ) ) );
	}

	/**
	 * We use mxcdn to import fontawesome because only whitelisted providers are allowed.
	 * See https://www.ampproject.org/docs/reference/spec
	 */
	public function action_amp_post_template_head() {
		echo '<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" >';
	}

	public function action_amp_post_template_css() {
		echo PMC::render_template( sprintf( '%s/templates/pmc-gallery-css.php', untrailingslashit( PMC_GOOGLE_AMP_ROOT ) ) );
	}

	/**
	* set_gallery_arr_keys | single-pmc-gallery.php
	*
	* @since 2017-06-07
	* @uses
	* @see
	*
	* @author brandoncamenisch
	* @version 2017-06-07 - bc-bug-list:
	* - Resets the array index values since they are being used for the next and
	* previous links.
	*
	* Example:
	*  Bad: [123]=> int(1) [456]=> int(2)
	*  Good: [0]=> int(1) [1]=> int(2)
	*
	* @param arr
	* @return arr
	**/
	protected function set_gallery_arr_keys( $arr ) {
		if ( is_array( $arr ) ) {
			$arr = array_values( $arr );
		}
		return $arr;
	}

	/**
	 * To render gallery slider instead of post content on AMP page.
	 *
	 * @hook  post_content
	 *
	 * @version 2017-07-14 Dhaval Parekh Added current slide title, caption and image credit in content.
	 *
	 * @param string $post_content Post content.
	 *
	 * @return string
	 */
	public function filter_pmc_post_amp_content( $post_content = '' ) {

		$gallery_attachment_image_slug = get_query_var( 'gallery-image' );
		$gallery = get_post();

		if ( empty( $gallery ) || is_wp_error( $gallery ) ) {
			return;
		}

		// Get all slide data.
		$pmc_cache = new PMC_Cache( ( self::PMC_GALLERY_CACHE_KEY . $gallery->ID ), self::PMC_GALLERY_CACHE_GROUP_KEY );
		$gallery_data = $pmc_cache->updates_with( array( $this, 'get_gallery_data' ), array( $gallery->ID ) )
							->get();

		if ( empty( $gallery_data ) || ! is_array( $gallery_data ) ) {
			return;
		}

		// Total number of slide.
		$total_images = count( $gallery_data );

		/**
		 * Check for current gallery image.
		 */
		if ( empty( $gallery_attachment_image_slug ) ) {
			$current_index = 0;
		} else {

			$current_index = array_search( $gallery_attachment_image_slug, wp_list_pluck( $gallery_data, 'attachment_name' ), true );
			$current_index = empty( $current_index ) ? 0 : $current_index;

		}

		$prev_index = $current_index - 1;
		$next_index = $current_index + 1;
		if ( 0 === $current_index ) {
			$prev_index = $total_images - 1;
		}
		if ( ( $total_images - 1 ) === $current_index ) {
			$next_index = 0;
		}

		$current_image_number = $current_index + 1;

		$attachment_image_html = wp_get_attachment_image( $gallery_data[ $current_index ]['attachment_id'], apply_filters( 'pmc_amp_gallery_image_size', 'post-thumbnail' ) );
		$gallery_image_html    = $this->get_amp_image_html( $attachment_image_html );

		$template      = PMC_GOOGLE_AMP_ROOT . '/templates/pmc-gallery.php';
		$template_args = array(
			'current_image_number'  => $current_image_number,
			'total_images'          => $total_images,
			'post_title'            => get_the_title(),
			'prev_gallery_url'      => $gallery_data[ $prev_index ]['amp_link'],
			'next_gallery_url'      => $gallery_data[ $next_index ]['amp_link'],
			'gallery_image_title'   => $gallery_data[ $current_index ]['image_title'],
			'gallery_image_caption' => $gallery_data[ $current_index ]['caption'],
			'gallery_image_credit'  => $gallery_data[ $current_index ]['image_credit'],
			'gallery_image_date'    => get_the_time( 'D, F j, Y g:ia T' ),
			'gallery_image_html'    => $gallery_image_html,
		);

		return \PMC::render_template( $template, $template_args );
	}

	/**
	 * Convert attachment html to amp image html using AMP_Img_Santizer
	 *
	 * @param string $attachment_image_html Attachment html.
	 *
	 * @return string $gallery_image_html Converted amp image html
	 */
	public function get_amp_image_html( $attachment_image_html ) {

		if ( ! $this->_is_amp() ) {
			return $attachment_image_html;
		}

		$dom                 = AMP_DOM_Utils::get_dom_from_content( $attachment_image_html );
		$amp_image_sanitizer = new AMP_Img_Sanitizer( $dom );
		$amp_image_sanitizer->sanitize();
		$gallery_image_html = AMP_DOM_Utils::get_content_from_dom( $dom );

		return $gallery_image_html;
	}

	/**
	 * To add gallery navigation event tracking.
	 *
	 * @param array $events List of event for AMP pages.
	 *
	 * @return array
	 */
	public function add_ga_event_tracking( $events = array() ) {

		if ( ! $this->_single_post->get_gallery_depth_event_tracking_status() ) {
			return $events;
		}

		if ( empty( $events ) || ! is_array( $events ) ) {
			$events = array();
		}

		$gallery = get_post();

		if ( empty( $gallery ) || is_wp_error( $gallery ) ) {
			return $events;
		}

		$pmc_cache = new PMC_Cache( ( self::PMC_GALLERY_CACHE_KEY . $gallery->ID ), self::PMC_GALLERY_CACHE_GROUP_KEY );
		$gallery_data = $pmc_cache->updates_with( array( $this, 'get_gallery_data' ), array( $gallery->ID ) )
							->get();

		if ( empty( $gallery_data ) || ! is_array( $gallery_data ) ) {
			return $events;
		}

		$current_slide = get_query_var( 'gallery-image' );

		if ( empty( $current_slide ) ) {
			$current_index = 1;
		} else {
			$current_index = array_search( $current_slide, wp_list_pluck( $gallery_data, 'attachment_name' ), true );
			$current_index = empty( $current_index ) ? 1 : ( $current_index + 1 );
		}

		$total_images = count( $gallery_data );

		$previous_index = $current_index - 1;
		$next_index = $current_index + 1;

		if ( 1 === $current_index ) {
			$previous_index = $total_images;
		}
		if ( $total_images === $current_index ) {
			$next_index = 1;
		}

		$events[] = array(
			'on'		 => 'click',
			'category'	 => 'standard-gallery',
			'selector'	 => '.pmc-amp-gallery-nav .pmc-amp-gallery-image-prev-button a',
			'label'		 => sprintf( '[M] left-arrow (from%dto%sof%d)', $current_index, $previous_index, $total_images ),
		);
		$events[] = array(
			'on'		 => 'click',
			'category'	 => 'standard-gallery',
			'selector'	 => '.pmc-amp-gallery-nav .pmc-amp-gallery-image-next-button a',
			'label'		 => sprintf( '[M] right-arrow (from%dto%sof%d)', $current_index, $next_index, $total_images ),
		);

		if ( ! $this->_is_gallery_thumbnail_enabled() ) {
			return $events;
		}

		foreach ( $gallery_data as $index => $item ) {

			$events[] = array(
				'on'       => 'click',
				'category' => 'standard-gallery',
				'selector' => sprintf( '.gallery-thumbnail-list > .gallery-thumbnail-item.thumbnail-%d a', ( $index + 1 ) ),
				'label'    => sprintf( '[M] thumbnail (from%dto%dof%d)', $current_index, ( $index + 1 ), $total_images ),
			);

		}

		return $events;
	}

	/**
	 * To add gallery thumbnail markup at end of content.
	 *
	 * @hook pmc_post_amp_content
	 *
	 * @param string $content Post content.
	 *
	 * @return string Gallery thumbnail markup.
	 */
	public function add_gallery_thumbnails( $content = '' ) {

		if ( ! $this->_is_gallery_thumbnail_enabled() ) {
			return $content;
		}

		// If it is not amp page then do not proceed.
		if ( ! $this->_is_amp() ) {
			return $content;
		}

		// Check if singuler page is for gallery or not.
		if ( ! is_singular( PMC_Gallery_Defaults::name ) ) {
			return $content;
		}

		$post = get_post();

		if ( empty( $post ) ) {
			return $content;
		}

		$pmc_cache = new PMC_Cache( ( self::PMC_GALLERY_CACHE_KEY . $post->ID ), self::PMC_GALLERY_CACHE_GROUP_KEY );
		$gallery_data = $pmc_cache->updates_with( array( $this, 'get_gallery_data' ), array( $post->ID ) )
							->get();

		if ( empty( $gallery_data ) ) {
			return $content;
		}

		$current_slide = get_query_var( 'gallery-image' );

		if ( empty( $current_slide ) ) {
			$current_slide = $gallery_data[0]['attachment_name'];
		}

		$template = sprintf( '%s/templates/gallery-thumbnails.php', untrailingslashit( PMC_GOOGLE_AMP_ROOT ) );

		$gallery_thumbnails_html = PMC::render_template( $template, array(
			'gallery_data'  => $gallery_data,
			'current_slide' => $current_slide,
		) );

		$gallery_thumbnails_html = $this->get_amp_image_html( $gallery_thumbnails_html );

		return $content . $gallery_thumbnails_html;
	}

	/**
	 * To get gallery post data.
	 *
	 * @global WP_Post $post Global Post.
	 * @param  int $post_id Gallery id.
	 *
	 * @return boolean|array Gallery datas.
	 */
	public function get_gallery_data( $post_id = false ) {

		if ( empty( $post_id ) ) {

			global $post;

			if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) ) {
				return false;
			}

			$post_id = $post->ID;
		}

		if ( PMC_Gallery_Defaults::name !== get_post_type( $post_id ) ) {
			return false;
		}

		$amp_var = 'amp';
		if ( defined( 'AMP_QUERY_VAR' ) ) {
			$amp_var = AMP_QUERY_VAR;
		}

		$reponse = array();

		$gallery_permalink = get_permalink( $post_id );

		$gallery_data = PMC_Gallery_View::fetch_gallery( $post_id );

		foreach ( $gallery_data as $index => $data ) {

			$attachment_id = absint( $data['ID'] );
			$attachment = get_post( $attachment_id );

			if ( empty( $attachment ) ) {
				continue;
			}

			$image = wp_get_attachment_image_src( $attachment_id, 'post-thumbnail' );
			$reponse[] = array(
				'attachment_id'   => $attachment_id,
				'attachment_name' => $attachment->post_name,
				'image_url'       => $image[0],
				'image_title'     => $data['title'],
				'amp_link'        => sprintf( '%s/%s/%s/', untrailingslashit( $gallery_permalink ), $attachment->post_name, $amp_var ),
				'link'            => sprintf( '%s#!%d/%s', untrailingslashit( $gallery_permalink ), ($index + 1 ), $attachment->post_name ),
				'caption'         => $data['caption'],
				'description'     => $data['description'],
				'image_credit'    => $data['image_credit'],
			);

		}

		return $reponse;
	}

}
