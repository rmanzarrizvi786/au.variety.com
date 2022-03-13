<?php

/**
 * Impelement related custom feed common features:
 *
 * Disable auto tagging
 * Disable auto embed
 * Show post type
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Feed_Features {

	use Singleton;

	private $_feed_options;

	function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
	}

	public function action_init() {
		add_action( 'pmc_custom_feed_start', array( $this, 'action_pmc_custom_feed_start' ), 10, 3 );
	}

	// action hook before feed template start
	public function action_pmc_custom_feed_start( $feed = false, $feed_options = false, $template = '' ) {

		$this->_feed_options = $feed_options;

		if ( !empty( $this->_feed_options['disable-autotag'] ) ) {
			add_filter( 'pmc_tag_links_enabled', '__return_false', 11 );
		}

		// auto embed?
		if ( !empty( $this->_feed_options['disable-autoembed'] ) && !empty( $GLOBALS['wp_embed'] ) ) {
			remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'autoembed'), 8 );
		}

		if ( !empty( $this->_feed_options['show-post-type'] ) ) {
			add_filter( 'pmc_custom_feed_attr_item', array( $this, 'filter_pmc_custom_feed_attr_item' ), 10 );
		}

	}

	public function filter_pmc_custom_feed_attr_item( $attrs ) {
		$post = get_post();

		if ( empty( $post ) ) {
			return $attrs;
		}

		// add post type attribute?
		if ( !empty( $this->_feed_options['show-post-type'] ) ) {
			switch( $post->post_type ) {
				case 'post':
					$attrs['type'] = 'article';
					break;
				case 'pmc-gallery':
					$attrs['type'] = 'gallery';
					break;
				case 'variety_top_video':
					$attrs['type'] = 'video';
					break;
				default:
					$attrs['type'] = $post->post_type;
					break;
			}
		}
		return $attrs;
	}

}

PMC_Custom_Feed_Features::get_instance();

// EOF
