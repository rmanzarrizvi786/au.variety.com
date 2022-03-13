<?php

/**
 * Extract embeds into <media:embeds> node if the extract-embeds/'Extract embeds into media:embeds node' option is enabled
 *
 * @since 2015-03-18 Hau Vong Initial version.
 * @see PPT-4200
 *
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Feed_Extract_Embeds {

	use Singleton;

	private $_feed_options;
	private $_embed_links = array();

	function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
	}

	public function action_init() {
		add_action( 'pmc_custom_feed_start', array( $this, 'action_pmc_custom_feed_start' ), 10, 3 );
	}

	// action hook before feed template start
	public function action_pmc_custom_feed_start( $feed = false, $feed_options = false, $template = '' ) {

		$this->_feed_options = $feed_options;

		if ( empty( $this->_feed_options['extract-embeds'] ) ) {
			return;
		}

		add_action( 'pmc_custom_feed_item', array( $this, 'action_pmc_custom_feed_item' ) );
		add_filter( 'pmc_custom_feed_post_start', array( $this, 'filter_pmc_custom_feed_post_start' ) );

		// Tap into WP_Embed filters to extrac the embed link
		add_filter( 'embed_handler_html', array( $this, 'extract_embed_link' ), 10, 2 );
		add_filter( 'embed_oembed_html', array( $this, 'extract_embed_link' ), 10, 2 );
		add_filter( 'embed_maybe_make_link', array( $this, 'extract_embed_link' ), 10, 2 );
	}

	/**
	 * Filter callback function to intercept embed html and extract the embed link into array $this->_embed_links
	 * @see WP_Embed::run_shortcode & WP_Embed::autoembed
	 * @param string $html The html output
	 * @param string $url The embed url link
	 * @return empty string
	 */
	public function extract_embed_link( $html, $url ) {
		$this->_embed_links[] = $url;
		return '';
	}

	/**
	 * Filter to intercept post feed start
	 * Run post content through WP_Embed::run_shortcode & WP_Embed::autoembed
	 * The embed_* filter we add earlier would intercept and extract the link
	 * and remove any embed link from the content
	 * @see PMC_Custom_Feed_Extract_Embeds::extract_embed_link
	 * @param WP_Post $post
	 * @return WP_Post
	 */
	public function filter_pmc_custom_feed_post_start( $post ) {
		// Need to check if post already processed by checking $post->embed_links
		if ( isset( $GLOBALS['wp_embed'] ) && ! isset( $post->embed_links ) ) {
			// we need to reset the variable to prevent it from carry over from previous post
			$this->_embed_links = array();
			$content = $post->post_content;
			$content = $GLOBALS['wp_embed']->run_shortcode( $content );
			$content = $GLOBALS['wp_embed']->autoembed( $content );
			$post->post_content = $content;
			// set the post embed_links that we just extracted
			$post->embed_links = $this->_embed_links;
		}
		return $post;
	}

	// callback function to output <media:embeds> nodes
	public function action_pmc_custom_feed_item( $post ) {
		if ( empty( $post->embed_links ) ) {
			return;
		}
		echo '<media:embeds>';
		foreach ( array_unique( $post->embed_links ) as $link ) {
			printf( '<media:embed><link>%s</link></media:embed>', PMC_Custom_Feed_Helper::esc_xml( $link ) );
		}
		echo '</media:embeds>';
	}

}

PMC_Custom_Feed_Extract_Embeds::get_instance();
// EOF
