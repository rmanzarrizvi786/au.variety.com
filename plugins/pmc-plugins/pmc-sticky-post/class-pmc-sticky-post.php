<?php

use PMC\Global_Functions\Traits\Singleton;

class PMC_Sticky_Post
{

	use Singleton;

	protected $_blogroll_sticky_position;

	protected $_sticky_post;

	protected $_blogroll_post_per_page;


	/**
	 * initialize class.
	 */
	protected function __construct()
	{

		$this->initialize_options();

		add_action('admin_init', array($this, 'maybe_add_term'), 10);

		add_action('pre_get_posts', array($this, 'pre_get_posts'));

		if (!is_admin()) {
			add_filter('the_posts', array($this, 'inject_sticky_post'), 10, 2);
		}
	}




	/**
	 *
	 */
	public function maybe_add_term()
	{

		$term_slug = apply_filters('pmc_sticky_post_term_slug', 'hl-sticky-posts');

		$stick_post_term = term_exists($term_slug, '_post-options');

		if (is_null($stick_post_term)) {
			// Let's make it a child term under 'Global Options'..
			$global_options_term = get_term_by('slug', 'global-options', '_post-options');

			$global_options_term_id = false !== $global_options_term ? $global_options_term->term_id : 0;

			$term_label = apply_filters('pmc_sticky_post_term_label', 'Sticky Post');


			// Create the exclusion term..
			wp_insert_term(
				$term_label,
				'_post-options',
				array(
					'description' => 'Taxonomy groups the Posts you want stuck in a position in the blogroll',
					'parent' => $global_options_term_id,
					'slug' => $term_slug,
				)
			);
		}
	}

	public function initialize_options()
	{

		$this->_blogroll_sticky_position = apply_filters('pmc_blogroll_sticky_position', 2);

		$offset = get_option('posts_per_page') - 1;

		if ($offset > 0  && $offset < 20) {
			$this->_blogroll_post_per_page = intval($offset);
		} else {
			$this->_blogroll_post_per_page = 20;
		}
	}

	/**
	 * get the top most sticky post.
	 */
	public function set_sticky_post()
	{

		// need to do this to avoid infinite loop
		remove_action('pre_get_posts', array($this, 'pre_get_posts'));
		//get all public custom post types
		$custom_types = get_post_types(array(
			'public' => true,
			'_builtin' => false,
		), 'names');

		$sticky_post_types = apply_filters('pmc-post-options-allowed-types', array_filter(array_unique(array_merge(array('post'), (array) $custom_types))));

		$term_slug = apply_filters('pmc_sticky_post_term_slug', 'hl-sticky-posts');

		$args = array(
			'posts_per_page'   => 1,
			'offset'           => 0,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_type'        =>  $sticky_post_types,
			'post_status'      => 'publish',
			'tax_query' => array(
				array(
					'taxonomy' => '_post-options',
					'field' => 'slug',
					'terms' =>  $term_slug
				)
			)
		);

		$sticky_post_query = new WP_Query($args);

		$sticky_post = $sticky_post_query->get_posts();

		add_action('pre_get_posts', array($this, 'pre_get_posts'));


		if (is_array($sticky_post)) {
			$sticky_post = $sticky_post[0];
		}

		$this->_sticky_post = $sticky_post;
	}

	/**
	 * @return mixed
	 * return the sticky post.
	 */
	public function get_sticky_post()
	{
		if (!isset($this->_sticky_post)) {
			$this->set_sticky_post();
		}

		return $this->_sticky_post;
	}

	/**
	 * @param $query
	 * @return mixed
	 * if the current page is the home page and we are on page one,
	 * set up the marked sticky post, exclude it from the query,
	 * reduce the post per page to 9 to account for the sticky post that
	 * will be injected upon render.
	 */
	public function pre_get_posts($query)
	{

		if (!$query->is_main_query()) {
			return $query;
		}

		if (is_home()) {

			$this->set_sticky_post();

			if (isset($this->_sticky_post) && isset($this->_sticky_post->ID)) {
				//1. exclude the sticky post from the query
				$query->set('post__not_in', array($this->_sticky_post->ID));

				//2 reduce the amount of posts that will be shown on this page to account for the sticky post.
				$query->set('posts_per_page', $this->_blogroll_post_per_page);
			}
		}

		return $query;
	}


	/**
	 * @param $posts
	 *
	 * @return array
	 * inject the sticky post to the posts for the blog roll.
	 */
	public function inject_sticky_post($posts, $query)
	{
		if (!$query->is_main_query()) {
			return $posts;
		}

		if (!$query->is_home()) {
			return $posts;
		}

		if (!is_array($posts)) {
			$posts = array();
		}
		/**
		 * we only want to inject a post when we have enough content to begin with.
		 */
		if (count($posts) > $this->_blogroll_sticky_position && isset($this->_sticky_post)) {

			array_splice($posts, $this->_blogroll_sticky_position, 0, array($this->_sticky_post));
		}

		return $posts;
	}
}

PMC_Sticky_Post::get_instance();
