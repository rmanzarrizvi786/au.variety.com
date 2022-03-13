<?php
/*
Plugin Name: PMC Footer
Plugin URI: https://www.pmc.com/
Description:renders out default feeds for the footer and allows LOBs to customize the feeds and image sizes. This plugin will eventually be moved to pmc-master when all LOBs begin using pmc-master
Version: 1.0.0
License: PMC Proprietary.  All rights reserved.
*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

/**
 * Gets the feed given and returs HTML output for the
 *
 * @param string $feed_source URL of valid feed
 * @param string $feed_title Name of feed
 */
function pmc_master_get_footer_feed( $feed_source, $feed_title, $css_classes = array() ) {

	$cache_key = sanitize_key( $feed_source );
	$callback  = apply_filters( 'pmc_master_footer_feed_callback', 'pmc_master_render_footer_feed' );

	$callback_params = array(
		'feed_source_url' => $feed_source,
		'feed_title'      => $feed_title,
		'css_classes'     => $css_classes,
	);

	$footer_pmc_cache = new PMC_Cache( $cache_key );

	$cache_data = $footer_pmc_cache->expires_in( 3600 ) // 1 hour
									->updates_with( $callback, array( $callback_params ) )
									->get();

	if ( $cache_data ) {
		echo $cache_data;
	}

}
/**
 * @param $feed_source
 * @param $feed_title
 * wrapper function for pmc_master_get_footer_feed
 */
function pmc_get_footer_feed( $feed_source, $feed_title, $css_classes = array() ) {
	pmc_master_get_footer_feed( $feed_source, $feed_title, $css_classes );
}

function pmc_master_render_footer_feed( $args ) {

	$default_css_classes = [ 'logos' ];

	$feed_title = ( ! isset( $args['feed_title'] ) ) ? '' : $args['feed_title'];

	// sanitize feed source
	$feed_source = esc_url_raw( $args['feed_source_url'], [ 'http', 'https' ] );

	if ( ! $feed_source ) {
		return false;
	}

	$site_title_css_class = str_replace( ' ', '', strtolower( $feed_title ) );

	if ( empty( $args['css_classes'] ) || ! is_array( $args['css_classes'] ) ) {
		$args['css_classes'] = [ sprintf( '%s-logo', $site_title_css_class ) ];
	}

	$css_classes = array_filter( array_unique( array_merge( $args['css_classes'], $default_css_classes ) ) );
	$css_classes = implode( ' ', $css_classes );

	//Parse the feed_source_url to determine the host of the URL. We render differently for you tube feed.
	$url_parts = wp_parse_url( $feed_source );
	$url_host  = isset( $url_parts['host'] ) ? $url_parts['host'] : '';

	add_filter( 'wp_feed_cache_transient_lifetime', 'pmc_set_transient_to_thirty_minutes' );
	$feed = fetch_feed( $feed_source );
	remove_filter( 'wp_feed_cache_transient_lifetime', 'pmc_set_transient_to_thirty_minutes' );

	if ( is_wp_error( $feed ) ) {
		return false;
	}

	$max_items = $feed->get_item_quantity( 1 );
	$rss_items = $feed->get_items( 0, $max_items );

	// Allow LOB to alter structure w/o overriding the entire function
	$feed_content = apply_filters( 'pmc_footer_feed_content', '', $rss_items, $feed_title );
	if ( $feed_content ) {
		return $feed_content;
	}

	// Allow LOB to alter the image size w/o overriding the entire function
	list( $image_width, $image_height ) = apply_filters( 'pmc_footer_feed_image_size', array( 180, 101 ) );

	// This is a loop, but it's really only looping over a single item
	foreach ( $rss_items as $item ) {
		// excerpt
		$content = $item->get_title();
		if ( '[galleria]' === $content ) { // killing the content if we have the gallery shortcode
			$content = '';
		}

		switch ( strtolower( $url_host ) ) {
			/*
			 * The youtube feed for the footer is different from our other feeds and
			 * therefore needs to be handled a little differently.
			 * We grab the thumbnail URl from the $item enclosure for the youtube feed
			 * and the the youtube thumbnail requires a play button overlay that the other
			 * feeds do not need.
			 */
			case "gdata.youtube.com":

				if ( $enclosure = $item->get_enclosure() ) {
					$image_src = function_exists( 'wpcom_vip_get_resized_remote_image_url' ) ? wpcom_vip_get_resized_remote_image_url( $enclosure->get_thumbnail(), $image_width, $image_height ) : $enclosure->get_thumbnail();
				}
				// Clobber existing $feed_content, we only want to show 1 story
				$feed_content = '
			<li  class="footer-sites '. esc_attr( $site_title_css_class ) .'">
				<a href="' . esc_url( $item->get_permalink() ) . '">
					<h5 class="' . esc_attr( $css_classes ) . '"></h5>
				<div class="entv-image">	<img src="' . PMC::esc_url_ssl_friendly( $image_src ) . '" width="' . intval( $image_width ) . '" height="' . intval( $image_height ) . '" alt="' . esc_attr( $args['feed_title'] ) . '" />
					<div class="entv-overlay-image"></div></div>
					<p>' . wp_kses_data( pmc_truncate( $content , 65 ) ) . '</p>
				</a>
			</li>
		';

				break;
			default:
				$image_src = pmc_master_get_footer_image( $item, $image_width, $image_height, $item->feed->feed_url );

				if ( empty( $image_src ) ) {
					//if we don't have an image in feed then use fallback image
					$image_src = plugins_url( 'images/trans.png', __FILE__ );
					$image_src = apply_filters( 'pmc_footer_feed_default_image', $image_src );
				}

				// Clobber existing $feed_content, we only want to show 1 story
				$feed_content = '
			<li class="footer-sites ' . esc_attr( $site_title_css_class ) . '">
				<a href="' . esc_url( $item->get_permalink() ) . '">
					<h5 class="' . esc_attr( $css_classes ) . '"></h5>
					<img src="' . PMC::esc_url_ssl_friendly( $image_src ) . '" width="' . intval( $image_width ) . '" height="' . intval( $image_height ) . '" alt="" />
					<p>' . wp_kses_data( \PMC::truncate( $content, 65 ) ) . '</p>
				</a>
			</li>
		';

		}



	}

	return $feed_content;

}   //end pmc_master_render_footer_feed()

/**
 * Called from the footer of each site. Each Site has a chance to change the order of feeds it needs to render
 * with the filter 'pmc_footer_feeds'
 */
function pmc_master_footer_feeds() {

	/*
	 * Ignoring below array in phpcs since phpcs is whining about too many spaces
	 * and mis-alignment, probably because it does not expect an array key to be so big.
	 *
	 * Proper fix for this would be to switch site names to array keys & URLs to array values.
	 * This will be a bit bigger task as it would likely require changes in all themes where
	 * this plugin is used.
	 *
	 * @since 2018-05-04 Amit Gupta
	 */
	// @codingStandardsIgnoreStart
	$default_feeds = [
		'https://variety.com/feed/pmc_footer/'                           => 'Variety',
		'https://deadline.com/feed/pmc_footer/'                          => 'Deadline',
		'https://bgr.com/feed/pmc_footer/'                               => 'BGR',
		'https://hollywoodlife.com/feed/pmc_footer/'                     => 'HollywoodLife',
		'https://footwearnews.com/custom-feed/pmc_footer/'               => 'FN',
		'https://gdata.youtube.com/feeds/api/playlists/B0B147258B247590' => 'Video',
	];
	// @codingStandardsIgnoreEnd

	$feeds = apply_filters( 'pmc_footer_feeds', $default_feeds );

	if ( empty( $feeds ) ) {
		return;
	}

	foreach ( $feeds as $feed => $title ) {
		pmc_master_get_footer_feed( $feed, $title );
	}

}

function pmc_master_get_footer_image( $item, $image_width, $image_height, $url ) {

	$image_src = '';
	$domain    = strtolower( wp_parse_url( $item->feed->feed_url, PHP_URL_HOST ) );

	$domains_list = [
		'www.deadline.com',
		'www.indiewire.com',
		'fusion.net',
		'fairchildlive.com',
		'spy.com',
		'hollywoodlife.com',
	];

	/**
	 * Allow to filter domains list to add feed image.
	 *
	 * @since 2017-08-04 Milind More CDWE-480
	 */
	$domains_list = apply_filters( 'pmc_footer_feed_image_domains', $domains_list );

	if ( is_array( $domains_list ) && in_array( $domain, (array) $domains_list, true ) ) {

		preg_match( '/(?<!_)src=([\'"])?(.*?)\\1/', $item->get_content(), $images );

		if ( ! empty( $images ) && isset( $images[2] ) && ! strpos( $images[2], 'sb.scorecardresearch.com' ) ) {
			$image_src = $images[2];
		}
	}

	//  Fallback to fetch thumbnail image from feed if didn't get from content
	if ( empty( $image_src ) ) {

		$enclosure = $item->get_enclosure();

		if ( ! empty( $enclosure ) ) {
			$image_src = $enclosure->get_thumbnail();
		}

		// fallback to media:thumbnail data node
		if ( empty( $image_src ) ) {
			$thumbnails = $item->get_item_tags( SIMPLEPIE_NAMESPACE_MEDIARSS, 'thumbnail' );
			if ( ! empty( $thumbnails ) ) {
				$thumbnails = array_filter( wp_list_pluck( $thumbnails, 'data' ) );
				if ( ! empty( $thumbnails ) ) {
					$image_src = reset( $thumbnails );
				}
			}
		}
	}

	if ( ! empty( $image_src ) ) {
		$image_src = add_query_arg(
			[
				'resize' => sprintf( '%d,%d', $image_width, $image_height ),
			],
			$image_src
		);
	}

	return $image_src;

}

/*
 * Enqueue  styles
 *	- taking care not to force site scripts on WP Admin sections
 *************************************************/
if ( ! is_admin() ) {
	function init_pmc_footer_styles() {
		// enqueue styles
		$pmc_footer_css_path = plugins_url( 'pmc-footer/css/', __DIR__ );
		wp_enqueue_style( 'pmcfooter', $pmc_footer_css_path . 'pmc-footer.css' );

	}

	// ok, now add the scripts in the init
	add_action( 'wp_enqueue_scripts', 'init_pmc_footer_styles', 10, 0 );
}

if ( ! function_exists( 'pmc_set_transient_to_thirty_minutes' ) ) {
	/**
	 * @return int
	 * Helper function to set the feed cache transient time to 30 mins as opposed to the default 12 hrs.
	 */
	function pmc_set_transient_to_thirty_minutes() {
		// change the default feed cache recreation period to 30 minutes
		return 1800;
	}
}

require_once( __DIR__ . '/class-pmc-footer.php' );
