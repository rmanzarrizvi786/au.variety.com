<?php

/**
 * Config class for JW Player.
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2018-04-24
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class JW_Player {

	use Singleton;

	/**
	 * Constuct Metod.
	 *
	 *
	 */
	public function __construct() {

		/**
		 * Actions
		 */
		add_action( 'add_meta_boxes', array( $this, 'add_video_box' ) );
	}

	/**
	 * To add JW Player Metabox in custom post types.
	 *
	 * @action admin_menu
	 *
	 * @return void
	 */
	public function add_video_box() {

		$post_types = [
			'variety_top_video',
			'variety_vip_video',
		];

		if ( get_option( 'jwplayer_show_widget' ) && get_option( 'jwplayer_api_key' ) && function_exists( 'jwplayer_media_widget_body' ) ) {
			add_meta_box( 'jwplayer-video-box', 'Insert media with JW Player', 'jwplayer_media_widget_body', $post_types, 'side', 'high' );
		}
	}

}
