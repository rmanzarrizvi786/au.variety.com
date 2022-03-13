<?php

namespace PMC\Affiliate_Links;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;
use \PMC_Custom_Feed;
use \DOMDocument;
use PMC\EComm\Tracking;

class Tagger {

	use Singleton;

	protected $_affiliates = array(
		'Amazon' => null,
		'Itunes' => null,
	);

	/**
	 * Construct
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_filter( 'the_excerpt_rss', array( $this, 'filter_the_excerpt_rss' ) );
		add_filter( 'the_content_feed', array( $this, 'filter_the_content_feed' ) ); // Adding this since `the_content_rss` is deprecated in 2.9.0
		add_filter( 'pmc_custom_feed_excerpt', array( $this, 'filter_the_excerpt_rss' ) );
		add_filter( 'the_content', array( $this, 'process_single_content' ) );

	}

	/*
	* This will allow us to more easily setup data for unit testing without using reflection.
	*/
	public function setup_data( $affiliate, $key, $value )
	{
		return $this->_affiliates[ $affiliate ]->conf[ $key ] = $value;
	}


	/**
	 * Init some stuff
 	 */
	public function init(){
		$this->load_affiliate_classes();
	}

	/**
	 * Loads affiliates we want to use
	 */
	public function load_affiliate_classes(){
		$this->_affiliates[ 'Amazon' ] = Amazon::get_instance();
		$this->_affiliates[ 'Itunes' ] = Itunes::get_instance();
	}

	/**
	 * Since we can't find what text should be links from a stripped down excerpt, we need to rebuild it and allow links
	 *
	 * @return string
	 */
	public function rebuild_excerpt_from_content( ){

		//Retrieve the post content.
		$content = get_the_content();

		//Delete all shortcode tags from the content.
		$content = strip_shortcodes( $content );

		$content = str_replace(']]>', ']]&gt;', $content);

		/**
		 * @change 2016-03-24 Corey Gilmore Temp Patch for all tags in feeds being stripped
		 * Remove this when fixed
		 */
		return $content;
		// Strip tags from content. We only want anchor tags to remain
		$allowed_tags = '<a>';

		return strip_tags( $content, $allowed_tags);

	}

	/**
	 * Process main body content without rebuilding it.
	 */
	public function filter_the_content_feed( $content ) {
		$is_custom_feed = PMC_Custom_Feed::get_instance()->is_feed();
		$feed_config = PMC_Custom_Feed::get_instance()->get_feed_config();

		if( is_feed() && $is_custom_feed ){
			return $this->process_custom_feed( $content, $feed_config );

		}elseif( is_feed() && ! $is_custom_feed ){
			return $this->process_main_feed( $content, false );

		}else{
			return $content;
		}
	}

	/**
	 * Detects if feed is main feed or if it's a custom feed
	 *
	 * @param $excerpt
	 * @return mixed|string|void
	 */
	public function filter_the_excerpt_rss( $excerpt ){

		$excerpt = $this->rebuild_excerpt_from_content();

		$is_custom_feed = PMC_Custom_Feed::get_instance()->is_feed();
		$feed_config = PMC_Custom_Feed::get_instance()->get_feed_config();

		if( is_feed() && $is_custom_feed ){
			return $this->process_custom_feed( $excerpt, $feed_config );

		}elseif( is_feed() && ! $is_custom_feed ){
			return $this->process_main_feed( $excerpt );

		}else{
			return $excerpt;
		}

	}

	/**
	 * Process content for custom feeds.
	 * Custom feeds that show excerpt will only have affiliate links preserved
	 *
	 * @param $content
	 * @param array $feed_config
	 * @return mixed
	 */
	public function process_custom_feed( $content, array $feed_config ){

		// Return raw excerpt if setting is turned off
		if( !empty( $feed_config ) && isset( $feed_config[ 'auto-tag-affiliate-links' ] ) ){
			if( $feed_config[ 'auto-tag-affiliate-links' ] ){
				return $this->process_single_content( $content );
			}
		}

		return $content;

	}

	/**
	 * Finds affiliate links in full content of feed entries.
	 * Displays full content instead of excerpt if at least one affiliate link is found.
	 *
	 * @param $excerpt
	 * @return mixed|string|void
	 */
	public function process_main_feed( $excerpt, $reset = true ){

		if ( ! is_feed() ) {
			return $excerpt;
		}

			// Get full content - we'll need to check for affiliate links
			if ( $reset ) {
				$content = get_the_content();
				$content = strip_shortcodes( $content );

				$display_content = wpautop( $content );
				$content_has_affiliate_link = false;
			} else {
				$display_content = $excerpt;
			}

		try {
			$doc = \PMC_DOM::load_dom_content( $display_content );

			// Load the HTML without declaring a doctype and strip special characters from the_content
			// @change 2016-03-24 Corey Gilmore temporarily disable this while troubleeshooting stripped links
			// $doc->loadHTML( esc_html( $display_content ), LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED );

			if( $doc === false ){
				return $excerpt;
			}

			$a_nodes = \PMC_DOM::get_dom_links( $doc );

			if( $a_nodes === false ){
				return $excerpt;
			}

			// Loop through affiliates - We'll check the affiliate status inside loop
			foreach ( $this->_affiliates as $affiliate => $affiliate_conf ) {

				if ( ! $affiliate_conf->is_affiliate_enabled() ) {
					continue;
				}

				// Loop through the <a> nodes - Nodes that match an affiliate url pattern will get replaced
				for ( $i = 0; $i < $a_nodes->length; ++$i ) {

					$a_node = $a_nodes->item( $i );
					$a_node_attributes  = array();
					if ( ! empty( $a_node->textContent ) && is_object( $a_node ) ) {

						// Get the attributes
						foreach ($a_node->attributes as $attrName => $attrNode) {
							$a_node_attributes[ $attrName ] = $a_node->getAttribute($attrName);
						}

						$url = $a_node->getAttribute( 'href' );

						if ( false === stripos( $url, $affiliate_conf->get_config( 'affiliate_pattern' ) ) ){
							continue;
						}

						// At least one link needs be an affiliate link
						$content_has_affiliate_link = true;

						// Break appart url - we'll force the 'query' to always only have the tag
						$tag_key = $affiliate_conf->get_config( 'tag_key' );
						$tag_value = $affiliate_conf->get_config( 'tag_value' );

						$params = array(
							$tag_key => $tag_value
						);

						$url = add_query_arg( $params, $url );
						$url = Tracking::get_instance()->track( $url );

						$a_node->setAttribute( 'href', $url );
					}
				}

			}	//foreach

			// Extract the HTML from the BODY tag
			$body = $doc->getElementsByTagName( 'body' )->item( 0 );
			$display_content = \PMC_DOM::domnode_get_innerhtml( $body );

			if( $content_has_affiliate_link ) {
				return $display_content;
			} else {
				return $excerpt;
			}

			} catch( \Exception $e ) {
				return PMC::maybe_throw_exception( $e->getMessage(), 'Exception', $excerpt );
			}

		return $excerpt;

	}

	/**
	 * @param $content
	 * @return mixed|string|void
	 */
	public function process_single_content( $content ) {

		$context = is_feed() ? 'feed' : ( is_single() ? 'single' : false );

		if( !$context ) {
			return $content;
		}

		// Take into account BGR landing pages
		if( function_exists( 'bgr_is_single' ) ){
			if( !bgr_is_single() && !is_feed() ){
				return $content;
			}
		}

		$display_content = wpautop( $content );

		try {
			$doc = \PMC_DOM::load_dom_content( $display_content );

			if( $doc === false ){
				return $content;
			}

			$a_nodes = \PMC_DOM::get_dom_links( $doc );

			if( $a_nodes === false ){
				return $content;
			}

			// Loop through affiliates - We'll check the affiliate status inside loop
			foreach ( $this->_affiliates as $affiliate => $affiliate_conf ) {

				// Is Affiliate enabled?
				if ( ! $affiliate_conf->is_affiliate_enabled() ) {
					continue;
				}

				// Loop through the <a> nodes - Nodes that match an affiliate url pattern will get replaced
				for ( $i = 0; $i < $a_nodes->length; ++$i ) {

					$a_node = $a_nodes->item( $i );
					$original_url = $a_node->getAttribute('href');

					// Bail if it's not an affiliate link
					if( false === stripos( $original_url, $affiliate_conf->get_config( 'affiliate_pattern' ) ) ){
						continue;
					}

					// Get affiliate tag and tag value from Cheezcap setting
					$tag_key = $affiliate_conf->get_config( 'tag_key' );
					$tag_value = $affiliate_conf->get_config( 'tag_value' );

					$params = array(
						$tag_key => $tag_value
					);

					// Append or override tag variable
					$url = add_query_arg( $params, $original_url );
					$url = Tracking::get_instance()->track( $url );

					$a_node->setAttribute('href', $url);

					// Add a tracking class (e.g. 'pmc-tagger-amazon')
					$bits = explode( '\\', strtolower( get_class( $affiliate_conf ) ) );
					$class_name = array_pop( $bits );
					$class_name = 'pmc-tagger-' . strtolower( $class_name );
					$classes = $a_node->getAttribute( 'class' );
					if ( $classes ) {
						$classes .= ' ';
					}
					$classes .= $class_name;
					$a_node->setAttribute( 'class', $classes );

				}

			}	//foreach

			// Extract the HTML from the BODY tag
			$body = $doc->getElementsByTagName( 'body' )->item( 0 );
			$display_content = \PMC_DOM::domnode_get_innerhtml( $body );
			return $display_content;

		} catch ( Exception $e ) {

			return PMC::maybe_throw_exception( $e->getMessage(), 'Exception', $content );

		}

	}	//process_single_content()


}	//end class


//EOF
