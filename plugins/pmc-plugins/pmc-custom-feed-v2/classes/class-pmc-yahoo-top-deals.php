<?php

/**
 * This class adds functionality to admin feed option yahoo top deals.
 */

namespace PMC\Custom_Feed;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Yahoo_Top_Deals
{

	use Singleton;

	const TODAYS_YAHOO_TOP_DEALS_TERM_NAME = 'Yahoo! - Today\'s Top Deals';
	const TODAYS_YAHOO_TOP_DEALS_TERM_SLUG = 'yahoo-todays-top-deals';
	const MORE_YAHOO_TOP_DEALS_TERM_NAME   = 'Yahoo! - More Top Deals';
	const MORE_YAHOO_TOP_DEALS_TERM_SLUG   = 'yahoo-more-top-deals';


	/**
	 * Class Constructor.
	 */
	protected function __construct()
	{
		$this->_setup_hooks();
	}

	/**
	 * Add action and filters hooks.
	 */
	protected function _setup_hooks()
	{

		add_action('admin_init', [$this, 'create_pmc_carousel_term']);
		add_filter('pmc_custom_feed_options_toggles', [$this, 'add_yahoo_top_deals_feed_options']);
		add_filter('pmc_custom_feed_content', [$this, 'inject_top_deals'], 9, 4); // execute earlier before custom_feed_html gets added to the_content

	}

	/**
	 * search and add, if needed, term for pmc_carousel
	 *
	 * @return void
	 */
	public function create_pmc_carousel_term(): void
	{

		if (class_exists('PMC_Carousel')) {

			$term_exists_todays_deals = term_exists(self::TODAYS_YAHOO_TOP_DEALS_TERM_SLUG, \PMC_Carousel::modules_taxonomy_name);

			if (null === $term_exists_todays_deals) {
				wp_insert_term(self::TODAYS_YAHOO_TOP_DEALS_TERM_NAME, \PMC_Carousel::modules_taxonomy_name, ['slug' => self::TODAYS_YAHOO_TOP_DEALS_TERM_SLUG]);
			}

			$term_exists_more_deals = term_exists(self::MORE_YAHOO_TOP_DEALS_TERM_SLUG, \PMC_Carousel::modules_taxonomy_name);

			if (null === $term_exists_more_deals) {
				wp_insert_term(self::MORE_YAHOO_TOP_DEALS_TERM_NAME, \PMC_Carousel::modules_taxonomy_name, ['slug' => self::MORE_YAHOO_TOP_DEALS_TERM_SLUG]);
			}
		}
	}

	/**
	 * Hook callback to add feed option.
	 *
	 * @param array $feed_options
	 * @return array
	 */
	public function add_yahoo_top_deals_feed_options($feed_options = []): array
	{

		return array_merge(
			$feed_options,
			[
				'yahoo-top-deals-top'    => 'Top Deals - Top',
				'yahoo-top-deals-bottom' => 'Top Deals - Bottom',
			]
		);
	}

	/**
	 * Renders yahoo top deals modules after 1st/2nd paragraph of article and end.
	 *
	 * @param $content
	 * @param $feed
	 * @param $post
	 * @param $feed_options
	 * @return string
	 */
	public function inject_top_deals($content, $feed, $post, $feed_options): string
	{

		if (empty($feed_options['yahoo-top-deals-top']) && empty($feed_options['yahoo-top-deals-bottom'])) {
			return $content;
		}

		// Get curated posts from carousel terms
		if (class_exists('PMC_Carousel')) {
			$todays_top_deals_curated_posts = pmc_render_carousel(\PMC_Carousel::modules_taxonomy_name, self::TODAYS_YAHOO_TOP_DEALS_TERM_SLUG, 3, '', ['add_filler' => false]);
			$more_top_deals_curated_posts   = pmc_render_carousel(\PMC_Carousel::modules_taxonomy_name, self::MORE_YAHOO_TOP_DEALS_TERM_SLUG, 3, '', ['add_filler' => false]);
		}

		if (!empty($feed_options['yahoo-top-deals-top'])) {

			$content_array = explode('</p>', wpautop($content));
			$location      = (count($content_array) < 2) ? 0 : 1; // index of paragraph where to insert module

			if (!empty($todays_top_deals_curated_posts) && count($todays_top_deals_curated_posts) >= 2) {

				$todays_top_deals_markup = $this->top_deals_module_markup($todays_top_deals_curated_posts, 'top');

				foreach ($content_array as $k => $v) {
					$content_array[$k] = $content_array[$k] . '</p>';

					if ($k === $location) {
						$content_array[$k] = $content_array[$k] . $todays_top_deals_markup;
					}
				}
			}

			$content = implode('', $content_array);
		}

		if (!empty($feed_options['yahoo-top-deals-bottom'])) {

			if (!empty($more_top_deals_curated_posts) && count($more_top_deals_curated_posts) >= 2) {
				$more_top_deals_markup = $this->top_deals_module_markup($more_top_deals_curated_posts, 'bottom');
				$content               = $content . $more_top_deals_markup;
			}
		}

		return $content;
	}

	/**
	 * Markup for list of top deals module
	 *
	 * @param array $posts
	 * @param string $location
	 */
	public function top_deals_module_markup($posts, $location = 'top')
	{

		$title = ('top' === $location) ? __('Today\'s Top Deals', 'pmc-custom-feed') : __('More Top Deals from ', 'pmc-custom-feed') . get_bloginfo('name');

		ob_start();

		echo '<div>';
		echo '<strong>' . esc_html($title) . '</strong>';
		echo '<ul>';
		foreach ($posts as $post) {
			echo '<li><a href="' . esc_url($post['url']) . '">' . esc_html($post['title']) . '</a></li>';
		}
		echo '</ul>';
		echo '</div>';

		return ob_get_clean();
	}
}

PMC_Yahoo_Top_Deals::get_instance();
