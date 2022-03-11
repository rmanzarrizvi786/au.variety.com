<?php

/**
 * Class Executive
 *
 * Handler for the exec templates.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Exec
 *
 * @since 2017.1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Executive
{

	use Singleton;

	/**
	 * Executive Data.
	 *
	 * @var $executive
	 */
	protected $executive;

	const EXEC_CREDIT_COUNT_LIMIT = 10;

	/**
	 * Class constructor.
	 */
	protected function __construct()
	{
		$this->executive = array(
			'job_title'        => '',
			'company_name'     => '',
			'twitter_handle'   => '',
			'twitter_url'      => '',
			'instagram_handle' => '',
			'instagram_url'    => '',
			'film_credits'     => '',
			'tv_credits'       => '',
			'related'          => array(),
			'photo_url'        => '',
			'photo_metadata'   => '',
		);

		add_action('wp', array($this, 'set_executive'), 10, 0);
		add_action('parse_query', array($this, 'override_posts_per_page'), 11);

		/*
		 * This adds the key and function associated so that the data
		 * can be fetched in single-hollywood-exec.php
		 *
		 * The call used to do that is
		 * PMC_Ajax_Pagination::html
		 */
		\PMC_Ajax_Pagination::add(array(
			'exec' => array($this, 'render_exec_river_posts'),
		));
	}

	/**
	 * Override Post Per Page
	 *
	 * The exec plugin sets the posts per page on the archive for executives to
	 * 50, we should set this back to default.
	 *
	 * @since 2017.1.0
	 * @action query_posts
	 * @param object $wp_query The WP Query object.
	 */
	public function override_posts_per_page($wp_query)
	{
		if (is_post_type_archive('hollywood_exec') && !is_admin()) {
			$wp_query->query_vars['posts_per_page'] = get_option('posts_per_page');
		}
	}

	/**
	 * Set Executive
	 *
	 * @since 2017.1.0
	 * @param integer|bool $post_id The ID of the post.
	 */
	public function set_executive($post_id = false)
	{
		if (!class_exists('Variety_Hollywood_Executives_API')) {
			return;
		}

		global $post;

		if (!$post || (!\Variety_Hollywood_Executives_API::POST_TYPE === $post->post_type && !$post_id)) {
			return;
		}

		// Get an API instance.
		$api = \Variety_Hollywood_Executives_API::get_instance();

		if (!$post_id) {
			$post_id = get_the_ID();
		}

		// Start with Profile information.
		$profile = $api->get_profile($post_id, array('companies', 'photo_metadata'));

		if (!empty($profile['companies'])) {
			$companies = $profile['companies'];
			$company   = array_shift($companies);

			if (!empty($company['jobs'])) {
				$this->executive['job_title'] = $company['jobs'];
			}

			$company_names   = array();
			$company_names[] = $company['company_name'];

			foreach ($companies as $company) {
				$company_names[] = $company['company_name'];
			}

			if (!empty($company_names)) {
				$this->executive['company_name'] = implode(', ', $company_names);
			}
		}

		// Get Social links.
		$social = $api->get_social($post_id);

		if (!empty($social['twitter_handle'])) {
			$this->executive['twitter_handle'] = $social['twitter_handle'];
			$this->executive['twitter_url']    = 'https://twitter.com/' . $social['twitter_handle'];
		}

		if (!empty($social['instagram_handle'])) {
			$this->executive['instagram_handle'] = $social['instagram_handle'];
			$this->executive['instagram_url']    = 'https://twitter.com/' . $social['instagram_handle'];
		}

		// Credits.
		$credits = $api->get_credits($post_id, true);
		$credits = $credits['credits'];

		if (!empty($credits['film'])) {
			$film_credits                    = $this->filter_credits($credits['film']);
			$this->executive['film_credits'] = !empty($film_credits) ? $film_credits : '';
		}

		if (!empty($credits['tv'])) {
			$tv_credits                    = $this->filter_credits($credits['tv']);
			$this->executive['tv_credits'] = !empty($tv_credits) ? $tv_credits : '';
		}

		if (!empty($credits['digital'])) {
			$digital_credits                    = $this->filter_credits($credits['digital']);
			$this->executive['digital_credits'] = (!empty($digital_credits)) ? $digital_credits : '';
		}

		// Get related profiles.
		$related = $api->get_related_profiles($post_id, 0, 3);

		if (!empty($related)) {
			$this->executive['related'] = $related;
		}

		$this->executive['photo_url']      = $profile['thumbnail']['src'];
		$this->executive['photo_metadata'] = $profile['photo_metadata'];
	}

	/**
	 * Filter Credits
	 *
	 * Filter the film and TV credit data. Ported from Variety 2014.
	 *
	 * @since 2017.1.0
	 * @param array $credits The credits to filer.
	 * @return array
	 */
	protected function filter_credits(array $credits)
	{
		$results        = [];
		$exclude_status = ['Active Development', 'Archived Development'];

		foreach ($credits as $credit_item) {

			// Validate for production status.
			if (!empty($credit_item['production_status']) && in_array($credit_item['production_status'], $exclude_status, true)) {
				continue;
			}

			// Validation for dates.
			if (!empty($credit_item['release_date'])) {
				$release_date = is_numeric($credit_item['release_date']) ? intval(is_numeric($credit_item['release_date'])) : strtotime($credit_item['release_date']);

				if ($release_date < 0) {
					continue;
				}
			} elseif (!empty($credit_item['air_date']) || !empty($credit_item['season_premiere_date'])) {

				$release_date = (!empty($credit_item['air_date'])) ? $credit_item['air_date'] : $credit_item['season_premiere_date'];
				$release_date = is_numeric($release_date) ? intval(is_numeric($release_date)) : strtotime($release_date);

				if (1 > $release_date) {
					continue;
				}
			}

			$results[] = $credit_item;
		} // End foreach.

		$results = array_splice($results, 0, self::EXEC_CREDIT_COUNT_LIMIT);

		return $results;
	}

	/**
	 * Render Exec River Posts
	 *
	 * Mostly ported from the 2014 theme.
	 *
	 * @since 2017.1.0
	 * @param integer $post_id The ID of the post.
	 * @param integer $post_offset The offset of posts.
	 * @param bool    $echo Output the data or not.
	 * @return array
	 */
	public function render_exec_river_posts($post_id, $post_offset, $echo = false)
	{
		$tags           = get_the_terms($post_id, 'post_tag');
		$html           = '';
		$posts_per_page = get_option('posts_per_page');

		if (!is_wp_error($tags) && is_array($tags) && count($tags) > 0) {
			$first_tag_id = $tags[0]->term_id;

			$args = array(
				'tag_id' => $first_tag_id,
				'posts_per_page' => $posts_per_page,
				'post_status' => 'publish',
			);

			if ($post_offset > 0) {
				$args['paged'] = $post_offset;
			}

			$query = new \WP_Query($args);

			if (!empty($query->posts)) {
				ob_start();
				foreach ($query->posts as $item) {
					\PMC::render_template(
						CHILD_THEME_PATH . '/template-parts/exec/river-item.php',
						compact('item')
					);
				}
				$html = ob_get_clean();
			}
		}

		return array(
			'html' => $html,
			'pages' => !empty($query) ? $query->max_num_pages : 0,
		);
	}

	/**
	 * Call
	 *
	 * Magic method to fetch exec data.
	 *
	 * @param string $name The name of the data variable.
	 * @param array  $args The arguments passed.
	 * @return null
	 */
	public function __call($name, $args)
	{
		$name = str_replace('get_', '', $name);

		if (array_key_exists($name, $this->executive)) {
			return $this->executive[$name];
		}

		return null;
	}
}
