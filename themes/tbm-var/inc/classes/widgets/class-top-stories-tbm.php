<?php

/**
 * Top Stories widget (TBM)
 *
 * @package pmc-variety-2017
 * @since 2022.08.02
 */

namespace Variety\Inc\Widgets;

use \PMC_Cache;
use Variety\Inc\Carousels;

class Top_Stories_TBM extends Variety_Base_Widget
{

	const ID = 'top-stories-tbm';

	/**
	 * Count - How many posts to display.
	 *
	 * @var int
	 */
	private $count = 6;

	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct()
	{
		parent::__construct(
			static::ID,
			__('Variety AU - Top Stories', 'pmc-variety'),
			array('description' => __('Displays most recent articles in TV, Film, Awards, and Digital', 'pmc-variety'))
		);
	}

	/**
	 * Method which returns data to be displayed on the front-end.
	 * The data here is not cached & this method is not meant to be called directly.
	 *
	 * @since 2017-10-04 Amit Gupta - CDWE-702
	 *
	 * @return array
	 *
	 */
	public function get_uncached_data($data = [])
	{

		if (!empty($data['count'])) {
			$this->count = $data['count'];
		}

		/* if (!empty($data['carousel'])) {
			$data['articles'] = Carousels::get_carousel_posts($data['carousel'], $this->count);
		} else {
			$data['articles'] = [];
		} */
		$posts = $this->get_top_stories_tbm($this->count);

		$data['articles'] = [];

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
						$data['articles'][]         = $carousel_item;
					}
				} else {

					$post_object = get_post($post['parent_ID']);

					if (!empty($post['url'])) {
						$post_object->url = $post['url'];
					}

					$post_object->parent_id = $post['parent_ID'];
					$data['articles'][]       = $post_object;
				}
			}
		}


		// echo '<pre>' . print_r($data['articles'], true) . '</pre>';

		return $data;
	}

	/**
	 * Get Fields.
	 *
	 * Get the option fields for this widget.
	 *
	 * @return array
	 *
	 */
	protected function get_fields()
	{
		return [
			'popular_count' => [
				'label' => __('Number Of Sidebar Popular Articles', 'pmc-variety'),
				'type'  => 'text',
			],
		];
	}

	private function get_top_stories_tbm($num_articles = 5)
	{

		// Never allow the cache to be flushed on the front-end
		if (!is_admin()) {
			$opts['flush_cache'] = false;
		}

		$num_articles = absint($num_articles);

		// Enforcing a range of articles 1-50, once available we should use PMC::numeric_range here
		if ($num_articles < 1 || $num_articles > 50)
			return false;

		$posts = array();

		global $post;
		$old_post = $post;

		// Get TOP STORY using taxonomy
		$args = array(
			'posts_per_page'  => 1,
			'post_type'       => 'post',
			'tax_query'      => array(
				array(
					'taxonomy'    => 'curation',
					'field' => 'slug',
					'terms'  => 'top-story',
				),
			),
		);

		$top_story_query = new \WP_Query($args);

		if ($top_story_query->have_posts()) {
			while ($top_story_query->have_posts()) {
				$top_story_query->the_post();
				$post_id = get_the_ID();
				$posts[$post_id] = $post_id;
			}
		}

		// Get rest of the stories which are not imported
		$args = array(
			'posts_per_page'  => $num_articles - count($posts),
			'post_type'       => 'post',
			'orderby'         => 'menu_order',
			'order'           => 'ASC',
			'meta_query' => array(
				array(
					'key' => 'imported_from',
					'compare' => 'NOT EXISTS'
				),
			)
		);

		$top_story_query = new \WP_Query($args);

		if ($top_story_query->have_posts()) {
			while ($top_story_query->have_posts()) {
				$top_story_query->the_post();
				$post_id = get_the_ID();
				$posts[$post_id] = $post_id;
			}
		}

		$output = array();

		foreach ($posts as $parent_post_id => $post_id) {
			if ($post = get_post($post_id)) {
				setup_postdata($post);

				// lets find the category with the most posts that is attached to the current post in the loop
				$categories = get_the_terms($post_id, 'vertical');
				$heaviest_cat = NULL;
				if (is_array($categories)) {
					foreach ($categories as $category) {
						if (NULL == $heaviest_cat) {
							$heaviest_cat = $category;
						} else {
							if ($category->count > $heaviest_cat->count) {
								$heaviest_cat = $category;
							}
						}
					}
				}

				$add = array(
					'ID'          => get_the_ID(),
					'post_id'     => get_the_ID(),
					'parent_ID'   => $parent_post_id,
					'title'       => get_the_title(),
					'url'         => get_permalink(),
					'date'        => get_the_date('c'),
					'author'      => get_the_author(),
					'author-url'  => get_author_posts_url(get_the_author_meta('ID')),
					'excerpt'     => get_the_excerpt(),
				);

				//if the carousal has a featured image, use that
				if (has_post_thumbnail($parent_post_id)) {
					$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($parent_post_id), '', false);
					if (!empty($thumb[0])) {
						$add['image'] = $thumb[0];

						// Image ID so that we can calculate srcset/sizes and fetch for the cached image.
						$add['image_id'] = get_post_thumbnail_id($parent_post_id);
						if (class_exists('PMC')) {
							$add['image_alt'] = \PMC::get_attachment_image_alt_text(get_post_thumbnail_id($parent_post_id), $parent_post_id);
						}
					}
				}

				//if we don't have featured image yet, use the post's if its there
				if ((!isset($add['image']) || empty($add['image'])) && has_post_thumbnail()) {
					$thumb = wp_get_attachment_image_src(get_post_thumbnail_id(), '', false);
					if (!empty($thumb[0])) {
						$add['image'] = $thumb[0];

						// Image ID so that we can calculate srcset/sizes and fetch for the cached image.
						$add['image_id'] = get_post_thumbnail_id();
						if (class_exists('PMC')) {
							$add['image_alt'] = \PMC::get_attachment_image_alt_text(get_post_thumbnail_id());
						}
					}
				}

				if (NULL != $heaviest_cat) {
					// see if we need to suppress this display
					if (apply_filters('pmc_carousel_category_with_most_posts', TRUE)) {
						$add['category'] = $heaviest_cat->name;
						$add['category-url'] = get_term_link($heaviest_cat);
						$add['category-slug'] = $heaviest_cat->slug;
					}
				}

				if ($parent_post_id != $post_id) {
					$parent_post = get_post($parent_post_id);

					if (!empty($parent_post->post_excerpt))
						$add['excerpt'] = apply_filters('the_excerpt', $parent_post->post_excerpt);

					if (!empty($parent_post->post_title))
						$add['title'] = apply_filters('the_title', $parent_post->post_title);
				}


				if (function_exists('variety_fetch_author')) {
					$author = variety_fetch_author(false, array('exclude-thumb' => true));
					if ($author != null) {
						$add['author'] = $author['name'];
						$add['author-url'] = $author['url'];
					}
				}

				// $add = (object) $add;

				$output[get_the_ID()] = $add;
			}
		}


		wp_reset_postdata();
		$post = $old_post;

		// echo '<pre>' . print_r($output, true) . '</pre>';

		return $output;
	}
}

//EOF
