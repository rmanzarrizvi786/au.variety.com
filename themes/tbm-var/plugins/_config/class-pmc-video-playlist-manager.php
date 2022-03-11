<?php
/**
 * Configuration for PMC Video Playlist Manager plugin.
 *
 * @author Jignesh Nakrani <jignesh.nakrani@rtcamp.com>
 *
 * @since 2018-04-04 READS-1104
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Video_Playlist_Manager {

	use Singleton;

	/**
	 * Class Constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Add action and filters hooks.
	 */
	protected function _setup_hooks() {
		/**
		 * Filters.
		 */
		add_filter( 'pmc_video_playlist_manager_targeted_taxonomies', [ $this, 'vpm_targeted_taxonomies' ] );
		add_filter( 'pmc_video_playlist_manager_playlist_taxonomy', [ $this, 'vpm_playlist_taxonomy' ] );
		add_filter( 'pmc_video_playlist_manager_video_posttype', [ $this, 'vpm_video_posttype' ] );
		add_filter( 'pmc_video_playlist_manager_article_posttype', [ $this, 'vpm_article_posttype' ] );
		add_filter( 'pmc_video_playlist_manager_parent_menu_slug', [ $this, 'vpm_parent_menu_slug' ] );
		add_filter( 'pmc_video_playlist_manager_video_meta_key', [ $this, 'vpm_video_meta_key' ] );
		add_filter( 'pmc_video_playlist_manager_video_thumbnail_size', [ $this, 'vpm_video_thumbnail_size' ] );

	}

	/**
	 * Filters taxonomies list for target page rule
	 *
	 * Example: array(
	 *     'name to display on admin page' => 'taxonomy_slug',
	 * )
	 *
	 * @return array the Targeted taxonomies
	 */
	public function vpm_targeted_taxonomies( $targeted_taxonomies ) {

		return [
			'verticals' => 'vertical',
			'tags'      => 'post_tag',
		];
	}

	/**
	 * Filter to  to override 'playlist taxonomy' slug
	 *
	 * @return string the Playlist taxonomy slug
	 */
	public function vpm_playlist_taxonomy( $playlist_taxonomy ) {

		return 'vcategory';
	}

	/**
	 * Filter to override 'Video' post type slug
	 *
	 * @return string returns Video postType slug
	 */
	public function vpm_video_posttype( $video_posttype ) {

		return 'variety_top_video';
	}

	/**
	 * Filter to override 'article post type' where Video module suppose to render
	 *
	 * @return array the article(Frontend posts to inject video module) postType slug
	 */
	public function vpm_article_posttype( $article_posttype ) {

		return [
			'post',
		];
	}

	/**
	 * Filters Parent menu slug to add Video playlist manager config page
	 *
	 * @return string returns Parent menu slug to add Video playlist manager config page
	 */
	public function vpm_parent_menu_slug( $parent_menu_slug ) {

		return 'curation';
	}

	/**
	 * Filters video source meta key to add Video in playlist
	 *
	 * @return string returns video source meta key
	 */
	public function vpm_video_meta_key( $meta_key ) {

		return 'variety_top_video_source';
	}

	/**
	 * Filters thumbnail size
	 *
	 * @return string returns thumbnail size
	 */
	public function vpm_video_thumbnail_size( $size ) {

		return 'landscape-large';
	}
}
