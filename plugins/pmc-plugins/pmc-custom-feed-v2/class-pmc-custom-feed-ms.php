<?php

/**
 * Class PMC_Custom_Feed_MS
 *
 * This class implement override for MS custom feed specific requirements
 *
 * @author PMC, Hau Vong
 * @version 2014-07-24
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Feed_MS {

	use Singleton;

	const FILTER_HTML_BLOCKLIST = 'pmc_custom_feed_ms_html_blocklist';

	protected function __construct() {
		add_action( 'pmc_custom_feed_start', array( $this, 'action_pmc_custom_feed_start' ), 10, 3 );
	}

	// action hook before feed template start
	public function action_pmc_custom_feed_start( $feed = false, $feed_options = false, $template = '' ) {

		// The 'MS Custom Feed' Custom Feed Option must be selected
		if ( empty( $feed_options['msfeed'] ) ) {
			return;
		}

		add_filter( 'pmc_custom_feed_title', 'strip_tags' );
		add_filter( 'pmc_custom_feed_post_title', 'strip_tags' );
		add_filter( 'the_excerpt_rss', 'strip_tags' );
		add_filter( 'excerpt_more', '__return_empty_string', 11 );
		add_filter( 'pmc_custom_feed_content', array( $this, 'filter_pmc_custom_feed_content' ) );
		add_filter( 'pmc_custom_feed_thumbnail_image_url', array( $this, 'pmc_custom_feed_thumbnail_image_url' ), 10, 2 );
		add_filter( 'pmc_custom_feed_rss_namespace', array( $this, 'filter_pmc_custom_feed_rss_namespace' ) );
		add_filter( 'embed_oembed_html', array( $this, 'filter_embed_html' ), 10, 2 );
		add_filter( 'embed_handler_html', array( $this, 'filter_embed_html' ), 10, 2 );
		add_filter( 'pmc_custom_feed_esc_xml_strict', '__return_true' );

		add_action( 'pmc_custom_feed_item', array( $this, 'action_pmc_custom_feed_item' ), 10, 2 );
	}

	public function filter_pmc_custom_feed_rss_namespace( $namespaces ) {
		if ( ! is_array( $namespaces ) ) {
			$namespaces = array();
		}
		$namespaces['atom'] = 'http://www.w3.org/2005/Atom';
		return $namespaces;
	}

	public static function action_pmc_custom_feed_item( $post, $feed_options = false ) {
		PMC_Custom_Feed_Helper::render_atom_category( 'atom:category' );
	}

	public function pmc_custom_feed_thumbnail_image_url( $img_url, $post = 0 ) {
		$post = get_post( $post );

		if ( empty( $post ) ) {
			return $img_url;
		}

		$thumbnail_id = get_post_thumbnail_id( $post->ID );

		if ( empty( $thumbnail_id ) ) {
			return $img_url;
		}

		$image = wp_get_attachment_image_src( $thumbnail_id, 'full' );

		if ( empty( $image[0] ) ) {
			return;
		}

		$img_url = $image[0];

		return $img_url;
	}

	public function filter_pmc_custom_feed_content( $content ) {

		$blocklist = array(
			'script',
			'style',
			'iframe',
			'embed',
		);

		// @TODO: SADE-517 to be removed
		$blocklist = apply_filters( 'pmc_custom_feed_ms_html_blacklist', $blocklist );
		$blocklist = apply_filters( self::FILTER_HTML_BLOCKLIST, $blocklist );

		array_walk(
			$blocklist,
			function( &$value ) {
				$value = preg_quote( $value, '@' );
			}
		);

		// strip content block: script, style, iframe, embed
		$content = preg_replace(
			sprintf( '@<(%1$s)[^>]*?>.*?</\\1>@si', implode( '|', (array) $blocklist ) ),
			'',
			$content
		);

		return $content;
	}

	/**
	 * Helper function to output the <media:content> nodes for the MSN Gallery feed
	 *
	 * @since 2014-12-24 Archana Mandahre
	 * @version 2015-12-03 - Javier Martinez - PMCVIP-555 - Added <media:description> node
	 * @version 2017-01-24 - Chandra Patel - CDWE-114 - Added <mi:hasSyndicationRights> node
	 * @ticket PPT-3925
	 * @version 2017-05-02 - Chandra Patel - CDWE-286
	 * @version 2019-06-12 - Kelin Chauhan - SADE-210 - Added gallery content to first slide.
	 *
	 * @param object $post Post object.
	 * @param array  $feed_options Custom feed options.
	 */
	public static function render_gallery_image_nodes( $post, $feed_options ) {

		// get all the image attachments.
		$image_attachments = PMC_Custom_Feed_Helper::get_gallery_images( $post->ID );

		// Include featured image as first slide in the gallery.
		if ( has_post_thumbnail( $post ) ) {

			$featured_image_data = [];
			$featured_image      = get_post( get_post_thumbnail_id( $post ) );

			if ( is_a( $featured_image, 'WP_Post' ) ) {

				$featured_image_data['image']     = wp_get_attachment_image_url( $featured_image->ID, 'full' );
				$featured_image_data['credit']    = PMC_Custom_Feed_Helper::get_image_credit( $featured_image->ID );
				$featured_image_data['title']     = $featured_image->post_title;
				$featured_image_data['mime_type'] = $featured_image->post_mime_type;

				array_unshift( $image_attachments, $featured_image_data );
			}
		}

		$content_attached = false;

		echo "\n";
		foreach ( $image_attachments as $ix => $image ) {

			// NOTE: This overlaps with the pmc_custom_feed_thumbnail_gallery and pmc_custom_feed_thumbnail_image filters.
			$image = apply_filters( 'pmc_custom_feed_thumbnail_gallery', $image, $feed_options );

			if ( empty( $image ) ) {
				continue;  // @codeCoverageIgnore
			}

			$image_src    = add_query_arg( 'quality', '80', $image['image'] );
			$image_credit = $image['credit'];

			if ( ! empty( $image_credit ) ) {

				// render the slideshow description in the <media:description> tag of first slide. SADE-210.
				if ( ! $content_attached ) {
					$bufs = self::get_gallery_description( $post );
					if ( ! empty( $bufs ) ) {
						$image['caption'] = $bufs;
					}
					$content_attached = true;
				}

				$hide_media_title               = ! empty( $feed_options['msn-hide-image-title'] );
				$hide_media_content_description = ! empty( $feed_options['msn-hide-media-content-description'] );

				echo '<media:content';
				echo ' url="' . PMC_Custom_Feed_Helper::esc_xml( $image_src ) . '"'; // WPCS: XSS ok;
				echo ' type="' . PMC_Custom_Feed_Helper::esc_xml( $image['mime_type'] ) . '"'; // WPCS: XSS ok;

				if ( isset( $image['title'] ) && ( ! $hide_media_content_description ) ) {
					echo ' description="' . PMC_Custom_Feed_Helper::esc_xml( trim( $image['title'] ) ) . '"'; // WPCS: XSS ok; SADE-180
				}

				echo " medium=\"image\">\n";
				echo '<media:credit>' . PMC_Custom_Feed_Helper::esc_xml( trim( $image_credit ) ) . "</media:credit>\n"; // WPCS: XSS ok;

				// Don't display the image title if the 'MSN Feeds - Hide Image Title' feed option is selected.
				if ( isset( $image['title'] ) && ( ! $hide_media_title ) ) {
					echo '<media:title>';
					echo PMC_Custom_Feed_Helper::esc_xml( trim( wp_strip_all_tags( $image['title'] ) ) );  // WPCS: XSS ok;
					echo "</media:title>\n";
				}

				if ( isset( $image['caption'] ) ) {
					echo '<media:description>';
					echo PMC_Custom_Feed_Helper::esc_xml_cdata( trim( $image['caption'] ) );  // WPCS: XSS ok;
					echo "</media:description>\n";
				}

				if ( isset( $feed_options['msn-syndication-rights'] ) && true === $feed_options['msn-syndication-rights'] ) {
					echo "<mi:hasSyndicationRights>1</mi:hasSyndicationRights>\n";
				}

				echo "</media:content>\n";

			}
		}
	}

	/**
	 * Helper function to get gallery description to append to first slide of the gallery.
	 * Takes Gallery Description if it's empty then takes DEK field from Features metabx,
	 * if that's also empty then takes Post Content of post to which the gallery is linked.
	 *
	 * @param WP_Post $post Post Object.
	 *
	 * @return string
	 */
	public static function get_gallery_description( $post ) {

		$post = get_post( $post );

		if ( empty( $post ) || 'pmc-gallery' !== get_post_type( $post ) ) {
			return '';
		}

		// Get description from gallery post.
		$content = $post->post_content;

		// If gallery description is empty then fetch the excerpt ( DEK ).
		if ( empty( $content ) ) {
			$content = $post->post_excerpt;
		}

		// If DEK is empty then fetch the content of the post to which the gallery is assigned.
		if ( empty( $content ) ) {

			// Get post ID to which the gallery is attached.
			$linked_post_id = apply_filters( 'pmc_gallery_linked_post', false, $post->ID );

			if ( ! empty( $linked_post_id ) && intval( $linked_post_id ) > 0 && 'post' === get_post_type( $linked_post_id ) ) {
				$linked_post = get_post( $linked_post_id );
				if ( ! empty( $linked_post ) ) {
					$content = $linked_post->post_content;
				}
			}
		}

		if ( ! empty( $content ) ) {
			$content = apply_filters( 'the_content', PMC::strip_shortcodes( $content ) );
			$content = wp_kses( $content, [ 'p' => [] ] );
		}

		return $content;
	}

	/**
	 * Return empty oembed html except twitter / instagram / facebook embed which supported by MSN feed.
	 *
	 * @version 2017-06-05 CDWE-395
	 * @version 2019-07-01 SADE-241 Added suport for instagram and facebook embeds.
	 *
	 * @hook embed_handler_html
	 * @hook embed_oembed_html
	 *
	 * @param mixed  $html   The HTML result of embed/oembed.
	 * @param string $url    The attempted embed URL.
	 *
	 * @return string $html
	 */
	public function filter_embed_html( $html, $url ) {

		if ( empty( $html ) || empty( $url ) ) {
			return $html;
		}

		if (
			false === strpos( $url, 'twitter' ) &&
			false === strpos( $url, 'instagram' ) &&
			false === strpos( $url, 'facebook' )
		) {
			$html = '';
		}

		return $html;

	}

}


PMC_Custom_Feed_MS::get_instance();
// EOF
