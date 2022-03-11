<?php

/**
 * PMC_Spark Carousels
 *
 * Class for dealing with Carousel data.
 *
 * @package pmc-variety
 * @since   2019-02-23
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Carousels.
 *
 * @since 2019.2.23
 */
class Carousels
{

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct()
	{

		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks()
	{

		add_filter('pmc_carousel_items_fallback_args', [$this, 'fallback_args']);
		add_filter('pmc_carousel_items_fallback_latest_args', [$this, 'fallback_args']);
		add_filter('pmc_carousel_widget_thumbsizes', [$this, 'filter_pmc_carousel_widget_thumbsizes']);
		add_filter('pmc-linkcontent-sf-types', [$this, 'set_curation_taxonomies']);
		add_filter('pmc_carousel_widget_templates', [$this, 'carousel_widget_templates']);
	}

	/**
	 * Get Carousel Posts
	 *
	 * Returns a list of posts selected in the carousel.
	 *
	 * @param string  $carousel   The carousel id.
	 * @param integer $count      The number of articles to fetch.
	 * @param string  $size       Attachment size.
	 * @param bool    $taxonomy   Use the carousel taxonomy? True/False.
	 * @param bool    $add_filler Should allow filters.
	 *
	 * @return array The list of carousel posts
	 * @since 2019.03.01
	 *
	 */
	public function get_posts(
		$carousel,
		$count,
		$size = 'square-small',
		$taxonomy = false,
		$add_filler = false,
		$add_filler_all_posts = false
	) {

		if (empty($taxonomy)) {
			$taxonomy = \PMC_Carousel::modules_taxonomy_name;
		}

		$posts = pmc_render_carousel(
			$taxonomy,
			$carousel,
			$count,
			$size,
			[
				'add_filler'           => $add_filler,
				'add_filler_all_posts' => $add_filler_all_posts,
			]
		);

		return $posts;
	}

	/**
	 * Adds thumbnail sizes to the carousel widget.
	 *
	 * @param array $sizes Array of thumbnail sizes.
	 *
	 * @return array Newly compiled list of thumbnails.
	 */
	public function filter_pmc_carousel_widget_thumbsizes($sizes)
	{

		$sizes = [
			'landscape-xlarge',
			'landscape-large',
			'landscape-medium',
			'square-small',
			'variety-popular',
		];

		return $sizes;
	}

	/**
	 * Manage carousel post arguments.
	 *
	 * @param array $args Query arguments.
	 *
	 * @return mixed
	 */
	public function fallback_args($args)
	{

		$args['no_found_rows'] = true;

		$args['date_query'] = [
			'after' => '30 day ago',
		];

		if (is_post_type_archive('pmc_top_video')) {

			$args['post_type'] = 'pmc_top_video';

			$args['date_query'] = [
				'after' => '90 day ago',
			];
		} else {
			$args['es'] = true;
		}

		return $args;
	}

	/**
	 * Get Carousels
	 *
	 * Returns a list of carousels.
	 *
	 * @since 2017.1.0
	 * @return array The list of carousels
	 *
	 * @codeCoverageIgnore
	 */
	public static function get_carousels()
	{
		$carousels = array(
			'none' => __('No Carousel (hide)', 'pmc-variety'),
		);

		foreach (get_terms(\PMC_Carousel::modules_taxonomy_name, array('hide_empty' => false)) as $term) {
			$carousels[$term->slug] = $term->name;
		}

		return $carousels;
	}

	/**
	 * @codeCoverageIgnore
	 *
	 * Get Carousel Posts
	 *
	 * Returns a list of posts selected in the carousel.
	 *
	 * @param string      $carousel The carousel id.
	 * @param integer     $count    The number of articles to fetch.
	 * @param bool|string $taxonomy The taxonomy of the carousel.
	 * @param bool|string $filler How should we backfill posts.
	 *
	 * @return array The list of carousel posts
	 * @since 2017.1.0
	 */
	public static function get_carousel_posts($carousel, $count, $taxonomy = false, $filler = false)
	{

		$carousel_posts = [];

		if (class_exists('PMC_Carousel')) {
			if (!$taxonomy) {
				$taxonomy = \PMC_Carousel::modules_taxonomy_name;
			}

			$filler_tax = false;
			$filler_all = false;

			if ('post' === $filler) {
				$filler_tax = true;
				$filler_all = true;
			} elseif ('taxonomy' === $filler) {
				$filler_tax = true;
			}

			$posts = pmc_render_carousel(
				$taxonomy,
				$carousel,
				$count,
				'',
				[
					'add_filler'           => $filler_tax,
					'add_filler_all_posts' => $filler_all,
				]
			);

			if (!empty($posts)) {
				foreach ($posts as $post) {

					if ((!empty($post['ID'])) && (0 !== $post['ID']) && $post['parent_ID'] !== $post['ID']) {
						// Since the Carousel doesn't provide us a \WP_Post object, it's easier to create one.
						$carousel_item = get_post($post['ID']);

						if (!empty($carousel_item)) {
							$title = get_the_title($post['parent_ID']);

							if (!empty(trim($title))) {
								$carousel_item->post_title   = $title;
								$carousel_item->custom_title = $title;
							}

							$excerpt = \PMC\Core\Inc\Helper::get_the_excerpt($post['parent_ID']);

							if (!empty(trim($excerpt))) {
								$carousel_item->custom_excerpt = $excerpt;
							}

							if (has_post_thumbnail($post['parent_ID'])) {
								$carousel_item->image_id = $post['image_id'];
							}

							$carousel_item->parent_id = $post['parent_ID'];
							$carousel_posts[]         = $carousel_item;
						}
					} else {

						$post_object = get_post($post['parent_ID']);

						if (!empty($post['url'])) {
							$post_object->url = $post['url'];
						}

						$post_object->parent_id = $post['parent_ID'];
						$carousel_posts[]       = $post_object;
					}
				}
			}
		}

		return $carousel_posts;
	}

	/**
	 * Fetch Video Carousel
	 *
	 * @param string      $carousel The carousel name.
	 * @param int         $count Number of items to fetch.
	 * @param bool|string $taxonomy The taxonomy of the carousel.
	 * @param bool|string $filler How should we backfill posts.
	 * @return array
	 *
	 * @codeCoverageIgnore
	 */
	public static function get_video_carousel_posts($carousel, $count, $taxonomy = false, $filler = false)
	{

		if (empty($filler_post_types)) {
			$filler_post_types = 'variety_top_video';
		}

		// Add filters.
		$post_type_filter = function ($args) use ($filler_post_types) {
			$args['post_type'] = $filler_post_types;
			return $args;
		};

		add_filter('pmc_carousel_items_fallback_args', $post_type_filter);
		add_filter('pmc_carousel_items_fallback_latest_args', $post_type_filter);

		$_posts = self::get_carousel_posts($carousel, $count, $taxonomy, $filler);

		// Remove filters.
		remove_filter('pmc_carousel_items_fallback_args', $post_type_filter);
		remove_filter('pmc_carousel_items_fallback_latest_args', $post_type_filter);

		return $_posts;
	}

	/**
	 * Add taxonomies to carousel curation.
	 *
	 * @param array $taxonomies List of taxonomies.
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore
	 */
	public function set_curation_taxonomies($taxonomies)
	{
		$taxonomies[] = 'vcategory';
		return $taxonomies;
	}

	/**
	 * @codeCoverageIgnore
	 *
	 * Add carousel widget template, for more details checkout `pmc-carousel-widget` plugin.
	 *
	 * @param array $templates List of template.
	 *
	 * @return array List of template.
	 */
	public function carousel_widget_templates($templates)
	{

		if (empty($templates) || !is_array($templates)) {
			$templates = [];
		}

		$variety_templates = [
			'template-parts/widgets/static-toaster' => esc_html__('Static Toaster Template', 'pmc-variety'),
		];

		$templates = array_merge($templates, $variety_templates);

		return $templates;
	}
}
