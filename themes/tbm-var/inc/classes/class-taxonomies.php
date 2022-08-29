<?php

/**
 * Class Taxonomies
 *
 * Handles taxonomies on Variety.
 *
 * @package pmc-variety-2017
 * @since   2017.1.0
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

class Taxonomies
{

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * @since 2017.1.0
	 */
	protected function __construct()
	{

		$this->_setup_hooks();
	}

	/**
	 * To setup actions/filters.
	 *
	 * @return void
	 * @since  2017-09-13 - Dhaval Parekh - CDWE-626
	 *
	 */
	protected function _setup_hooks()
	{

		/**
		 * Filters
		 */
		add_filter('pmc_core_editorial_tax_object_types', [$this, 'setup_editorial_content_types']);
		add_filter('init', [$this, 'update_editorial_slug']);
		add_filter('init', [$this, 'register_taxonomy_curation']);
		add_filter('register_taxonomy_args', [$this, 'register_taxonomy_args'], 10, 2);
		add_filter('jetpack_open_graph_tags', [$this, 'trending_tv_og_tags'], 10, 2);
	}

	/**
	 * Setup Editorial Content Types
	 *
	 * Adds the editorial taxonomy to the post types it is required on.
	 *
	 * @param array $types The existing types.
	 *
	 * @return array
	 * @since 2017.1.0
	 */
	public function setup_editorial_content_types($types)
	{

		$types[] = 'tout';
		$types[] = 'variety_top_video';
		$types[] = 'variety_vip_post';
		$types[] = 'variety_vip_report';
		$types[] = 'variety_vip_video';

		return $types;
	}

	/**
	 * @codeCoverageIgnore
	 *
	 * Update Editorial Slug
	 *
	 * Changes the slug from /editorial/ to /e/
	 *
	 * @since 2017.1.0
	 */
	public function update_editorial_slug()
	{

		register_taxonomy(
			'editorial',
			apply_filters('pmc_core_editorial_tax_object_types', ['post', 'pmc-gallery', 'pmc_list', 'pmc-content']),
			[
				'label'             => __('Editorial', 'pmc-variety'),
				'labels'            => [
					'name'               => _x('Editorials', 'taxonomy general name', 'pmc-variety'),
					'singular_name'      => _x('Editorial', 'taxonomy singular name', 'pmc-variety'),
					'add_new_item'       => __('Add New Editorial', 'pmc-variety'),
					'edit_item'          => __('Edit Editorial', 'pmc-variety'),
					'new_item'           => __('New Editorial', 'pmc-variety'),
					'view_item'          => __('View Editorial', 'pmc-variety'),
					'search_items'       => __('Search Editorials', 'pmc-variety'),
					'not_found'          => __('No Editorials found.', 'pmc-variety'),
					'not_found_in_trash' => __('No Editorials found in Trash.', 'pmc-variety'),
					'all_items'          => __('Editorials', 'pmc-variety'),
				],
				'query_var'         => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
				'rewrite'           => [
					'slug'       => 'e',
					'with_front' => false,
				],
				'capabilities'      => [
					'manage_terms' => 'manage_options',
					'edit_terms'   => 'manage_options',
					'delete_terms' => 'manage_options',
					'assign_terms' => 'edit_posts',
				],
				'show_in_menu'      => false,
				'show_in_nav_menus' => false,
				'show_admin_column' => false,
			]
		);
	}

	/**
	 * @codeCoverageIgnore
	 *
	 * Register Taxonomy for Curation
	 */
	public function register_taxonomy_curation()
	{

		register_taxonomy(
			'curation',
			apply_filters('pmc_core_editorial_tax_object_types', ['post']),
			[
				'label'             => __('Curation', 'pmc-variety'),
				'labels'            => [
					'name'               => _x('Curations', 'taxonomy general name', 'pmc-variety'),
					'singular_name'      => _x('Curation', 'taxonomy singular name', 'pmc-variety'),
					'add_new_item'       => __('Add New Curation', 'pmc-variety'),
					'edit_item'          => __('Edit Curation', 'pmc-variety'),
					'new_item'           => __('New Curation', 'pmc-variety'),
					'view_item'          => __('View Curation', 'pmc-variety'),
					'search_items'       => __('Search Curations', 'pmc-variety'),
					'not_found'          => __('No Curations found.', 'pmc-variety'),
					'not_found_in_trash' => __('No Curations found in Trash.', 'pmc-variety'),
					'all_items'          => __('Curations', 'pmc-variety'),
				],
				'query_var'         => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
				'capabilities'      => [
					'edit_terms'   => 'administrator',
					'manage_terms' => 'administrator',
					'delete_terms' => 'administrator',
					'assign_terms' => 'edit_posts',
				],
				'public' => false,
				'show_in_menu'      => true,
				'show_in_nav_menus' => false,
				'show_admin_column' => false,
			]
		);
	}

	/**
	 * To changes argument of taxonomy before it register.
	 * For change category slug.
	 *
	 * @param array $args Aruguments which will be passed for taxonomy registration.
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return array Aruguments which will be passed for taxonomy registration.
	 * @since  2017-09-13 - Dhaval Parekh - CDWE-626
	 *
	 * @hook   register_taxonomy_args
	 *
	 */
	public function register_taxonomy_args($args, $taxonomy)
	{

		if ('category' === $taxonomy) {
			$args['rewrite']['slug'] = 'c';
		}

		if ('post_tag' === $taxonomy) {
			$args['rewrite']['slug'] = 't';
		}

		return $args;
	}

	/**
	 * Custom og and twtitter cards for trending tv tag
	 *
	 * @param array $tags Array of Open Graph Meta tags from jetpack.
	 * @param array $args Array of image size parameters.
	 *
	 * @return array $tags Array of Open Graph Meta tags
	 * @since BR-1455
	 */
	public function trending_tv_og_tags($tags, $args)
	{
		if (is_tag('trending-tv')) {
			$settings = get_option('global_curation', []);
			$settings = $settings['tab_variety_trending_tv'];
			if (!empty($settings['variety_trending_social'])) {
				$tags['og:image']      = wp_get_attachment_image_url($settings['variety_trending_social'], 'landscape-large');
				$tags['twitter:image'] = $tags['og:image'];
			}

			// Description comes from pmc-seo-tweaks for taxonomy
			$taxonomy_term = pmc_get_option(\PMC\SEO_Tweaks\Taxonomy::option_name . get_queried_object_id());
			$description   = (!empty($taxonomy_term['description'])) ? sanitize_text_field($taxonomy_term['description']) : '';
			if (!empty($description)) {
				$tags['og:description']      = $description;
				$tags['twitter:description'] = $description;
			}
			// Larger image for the Twitter card
			$tags['twitter:card']      = 'summary_large_image';
			$tags['twitter:title']     = $tags['og:title'];
			$tags['twitter:image:alt'] = $tags['og:title'] . ' Logo';
		}

		return $tags;
	}
}
