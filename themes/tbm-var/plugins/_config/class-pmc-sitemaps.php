<?php
/**
 * Config file for pmc-sitemaps plugin
 *
 * @author  Chandra Patel <chandrakumar.patel@rtcamp.com>
 *
 * @since   2017-10-16
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Sitemaps {

	use Singleton;

	/**
	 * Setup various hooks
	 *
	 *
	 */
	protected function __construct() {

		add_filter( 'pmc_sitemaps_post_type_whitelist', [ $this, 'filter_sitemaps_post_type_whitelist' ] );

		add_filter( 'pmc_sitemaps_taxonomy_whitelist', [ $this, 'filter_sitemaps_taxonomy_whitelist' ] );

		add_filter( 'pmc_generate_archive_index', '__return_false' );

		add_filter( 'pmc_sitemap_exclude_post', [ $this, 'maybe_exclude_post' ], 12, 2 ); // This needs to be executed at later priority than default to avoid race condition”

		add_filter( 'jetpack_sitemap_news_sitemap_item', [ $this, 'filter_jetpack_news_sitemap_item' ], 99, 2 ); // we modifying the item array, so the priority number should be higher than the default one, other than it will not work

	}

	/**
	 * Return a other list of valid post types for pmc sitemap
	 *
	 * @param array $post_types An array of post types.
	 *
	 * @return array
	 */
	public function filter_sitemaps_post_type_whitelist( $post_types ) {

		if ( empty( $post_types ) || ! is_array( $post_types ) ) {
			$post_types = [];
		}

		$post_types = array_merge( $post_types, [
			'variety_top_video',
			'exclusive',
			\Variety\Plugins\Variety_VIP\Content::VIP_POST_TYPE,
			\Variety\Plugins\Variety_VIP\Content::VIP_VIDEO_POST_TYPE,
			\Variety\Plugins\Variety_VIP\Special_Reports::POST_TYPE,
		] );

		$post_types = array_diff( $post_types, [
			'vy-thought-leaders',
		] );

		$post_types = array_values( array_unique( (array) $post_types ) );

		return $post_types;

	}

	/**
	 * Return a other list of valid taxonomies for pmc sitemap
	 *
	 * @param array $taxonomies An array of taxonomies.
	 *
	 * @return array
	 */
	function filter_sitemaps_taxonomy_whitelist( $taxonomies ) {

		if ( empty( $taxonomies ) || ! is_array( $taxonomies ) ) {
			$taxonomies = [];
		}

		$taxonomies = array_merge( $taxonomies, [
			'vcategory',
			'vertical',
			\Variety\Plugins\Variety_VIP\Content::VIP_CATEGORY_TAXONOMY,
		] );

		$taxonomies = array_values( array_unique( (array) $taxonomies ) );

		$taxonomies = array_diff( $taxonomies, [ 'editorial' ] );

		return $taxonomies;

	}

	/**
	 * Filter a post in the PMC Sitemaps plugin to indicate if it should be
	 * excluded or not.
	 *
	 * @param boolean $current The current value.
	 * @param \WP_Post $post A post object.
	 *
	 * @return boolean True to exclude the post, or the current value.
	 */
	public function maybe_exclude_post( $current, $post ) {

		if ( false === (bool) $current ) {
			$dirt_cross_post  = get_post_meta( $post->ID, 'dirt_permalink', true );
			$exclude_from_seo = get_post_meta( $post->ID, '_pmc_seo_archive', true );

			if ( ! empty( $dirt_cross_post ) || 'on' === $exclude_from_seo ) {

				return true;
			}
		}

		return (bool) $current;
	}

	/**
	 * Filter news sitemap item to replace `%vertical%` from the post url with vertical term's slug.
	 *
	 * @param array $item_array Current post item.
	 * @param int $post_id post id.
	 *
	 * @return array $item_array Modified post item.
	 */
	public function filter_jetpack_news_sitemap_item( $item_array, $post_id ) {
		$item_array['url']['loc'] = preg_replace_callback(
			'/%vertical%/',
			function ( $matches ) use ( $post_id ) {
				$vertical = 'more'; // initialize to a default vertical
				if ( class_exists( PMC_Vertical::class ) ) {
					$term_obj = \PMC_Vertical::get_instance()->primary_vertical( $post_id );
					if ( ! empty( $term_obj ) ) {
						$vertical = $term_obj->slug;
					}
				}

				return $vertical;
			},
			$item_array['url']['loc']
		);

		return $item_array;
	}

}

//EOF
