<?php
/**
 * Related
 *
 * Responsible for related articles.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Variety_VIP;

use PMC\Automated_Related_Links\Plugin;
use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Related
 */
class Related {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() {
		add_action( 'wp', [ $this, 'vip_filters' ], 1 );
		add_action( 'load-post.php', [ $this, 'meta_box_setup' ] );
		add_action( 'load-post-new.php', [ $this, 'meta_box_setup' ] );
	}

	/**
	 * Setup VIP specific filters.
	 *
	 * @codeCoverageIgnore
	 */
	public function vip_filters() {
		if ( Content::is_vip_page() ) {
			add_filter( 'variety_related_post_types', [ $this, 'vip_related_post_type' ] );
			add_filter( 'variety_related_posts_template', [ $this, 'vip_related_template' ] );
			add_filter( 'variety_pre_get_related_items', [ $this, 'get_related_items' ] );
		}
	}

	/**
	 * Return related post types for VIP.
	 *
	 * @return array
	 */
	public function vip_related_post_type() {
		return [ Content::VIP_POST_TYPE ];
	}

	/**
	 * Return path to VIP related card.
	 *
	 * @return string
	 */
	public function vip_related_template() {
		return sprintf( '%s/template-parts/vip/article/related.php', untrailingslashit( CHILD_THEME_PATH ) );
	}

	/**
	 * Return VIP related items.
	 *
	 * @return array
	 */
	public function get_related_items() {
		$post_obj = get_queried_object();
		$items    = [
			'type'  => 'post',
			'posts' => [],
		];

		// Else get the primary taxonomy.
		if ( empty( $items['posts'] ) ) {
			$primary_term   = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $post_obj->ID, Content::VIP_CATEGORY_TAXONOMY );
			$items['posts'] = \Variety\Inc\Related::get_instance()->get_related( $primary_term );
		}

		return $items;
	}

	/**
	 * Actions to add meta boxes.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function meta_box_setup() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
	}

	/**
	 * Add the related meta box to VIP posts.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function add_meta_boxes() {
		add_meta_box( 'pmc-automated-related-links', __( 'Related Content', 'pmc-variety' ), [ Plugin::get_instance(), 'meta_box' ], Content::VIP_POST_TYPE, 'normal', 'core' );
	}

}

// EOF.
