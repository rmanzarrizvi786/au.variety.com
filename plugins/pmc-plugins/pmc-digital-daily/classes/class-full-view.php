<?php

/**
 * Digital Daily Full-Content View Feature.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Larva;
use WP_Post;

/**
 * Class Full_View.
 */
class Full_View
{
	use Singleton;

	/**
	 * Slug appended to permalinks to trigger full view.
	 */
	public const REWRITE_ENDPOINT = 'full-view';

	/**
	 * Query variable indicating full view was requested.
	 */
	public const QUERY_VAR = 'pmc-full-view';

	/**
	 * Permalink-filter details required to add, remove, and check the status of
	 * these hooks.
	 *
	 * @var array
	 */
	protected array $_permalink_filter_details = [
		'callback' => null,
		'filters'  => [
			'post_link',
			'page_link',
			'post_type_link',
		],
		'priority' => 10,
	];

	/**
	 * Check if current request is for full view.
	 *
	 * @return bool
	 */
	public static function is(): bool
	{
		return (bool) get_query_var(static::QUERY_VAR);
	}

	/**
	 * Retrieve permalink for a given issue's full view.
	 *
	 * @param WP_Post|int $id_or_object Post ID or post object.
	 * @return string
	 */
	public static function get_permalink($id_or_object = null): string
	{
		if (empty($id_or_object)) {
			$id_or_object = get_the_ID();
		}

		if (!static::_should_use_pretty_permalink($id_or_object)) {
			return add_query_arg(
				static::QUERY_VAR,
				1,
				get_permalink(
					$id_or_object
				)
			);
		}

		return user_trailingslashit(
			sprintf(
				'%1$s/%2$s',
				untrailingslashit(
					get_permalink(
						$id_or_object
					)
				),
				static::REWRITE_ENDPOINT
			)
		);
	}

	/**
	 * Retrieve permalink for a given post appearing in a particular issue.
	 *
	 * @param int $issue_id Digital Daily issue ID.
	 * @param int $post_id  Post ID.
	 * @return string|null
	 */
	public static function get_post_permalink(
		int $issue_id,
		int $post_id
	): ?string {
		if (POST_TYPE !== get_post_type($issue_id)) {
			return null;
		}

		return sprintf(
			'%1$s#%2$s',
			static::get_permalink($issue_id),
			Larva\get_id_attribute_for_post_id($post_id)
		);
	}

	/**
	 * Add permalink filters that direct visitors to the full view.
	 */
	public static function add_permalink_filters(): void
	{
		static::get_instance()->toggle_permalink_filters(true);
	}

	/**
	 * Remove permalink filters that direct visitors to the full view.
	 */
	public static function remove_permalink_filters(): void
	{
		static::get_instance()->toggle_permalink_filters(false);
	}

	/**
	 * Check if permalink filters are active.
	 *
	 * @return bool
	 */
	public static function permalink_filters_added(): bool
	{
		return static::get_instance()->check_permalink_filters();
	}

	/**
	 * Add content filters that convert to single-column view.
	 */
	public static function add_content_filters(): void
	{
		if (static::is()) {
			static::get_instance()->toggle_content_filters(true);
		} else {
			static::remove_content_filters();
		}
	}

	/**
	 * Remove content filters that convert to single-column view.
	 */
	public static function remove_content_filters(): void
	{
		static::get_instance()->toggle_content_filters(false);
	}

	/**
	 * Full_View constructor.
	 */
	protected function __construct()
	{
		$this->_permalink_filter_details['callback'] = [
			$this,
			'modify_permalinks',
		];

		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void
	{
		add_action('init', [$this, 'add_rewrite_endpoint']);
		add_filter('request', [$this, 'enforce_request_value']);
		add_filter(
			'redirect_canonical',
			[$this, 'prevent_canonical_redirect'],
			10,
			2
		);
		add_filter('pmc_page_meta', [$this, 'set_page_subtype']);
		add_filter('body_class', [$this, 'add_body_class']);
		add_filter(
			'pmc_gallery_close_button_link',
			[$this, 'override_gallery_close_button_link'],
			10,
			2
		);
	}

	/**
	 * Register rewrite endpoint to support full view.
	 */
	public function add_rewrite_endpoint(): void
	{
		add_rewrite_endpoint(
			static::REWRITE_ENDPOINT,
			EP_PERMALINK,
			static::QUERY_VAR
		);
	}

	/**
	 * Force value for our query var, so that `get_query_var()` works.
	 *
	 * @param array $qv Query variables.
	 * @return array
	 */
	public function enforce_request_value(array $qv): array
	{
		if (isset($qv[static::QUERY_VAR])) {
			$qv[static::QUERY_VAR] = true;
		}

		return $qv;
	}

	/**
	 * Prevent `redirect_canonical()` from interfering with full-view requests,
	 * while also ensuring that full-view URLs have trailing slashes.
	 *
	 * @param bool|string $redirect_url  Redirect destination.
	 * @param bool|string $requested_url Requested URL.
	 * @return bool|string
	 */
	public function prevent_canonical_redirect(
		string $redirect_url,
		string $requested_url
	) {
		global $wp_rewrite;

		if (!static::is()) {
			return $redirect_url;
		}

		if (
			!$wp_rewrite->using_permalinks()
			|| !str_ends_with(
				$wp_rewrite->permalink_structure,
				'/'
			)
		) {
			return false;
		}

		$path = wp_parse_url($requested_url, PHP_URL_PATH);
		if (!str_ends_with($path, '/')) {
			return str_replace($path, $path . '/', $requested_url);
		}

		return false;
	}

	/**
	 * Specify page meta indicating the view being rendered.
	 *
	 * @param array $meta PMC Page Meta data.
	 * @return array
	 */
	public function set_page_subtype(array $meta): array
	{
		if (!is_singular(POST_TYPE)) {
			return $meta;
		}

		$meta['page-subtype']  = $meta['page-type'] . '-';
		$meta['page-subtype'] .= static::is() ? 'full-content' : 'landing-page';

		return $meta;
	}

	/**
	 * Add body class indicating which view is being viewed.
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	public function add_body_class(array $classes): array
	{
		if (!is_singular(POST_TYPE)) {
			return $classes;
		}

		$classes[] = sprintf(
			'%1$s-%2$s',
			POST_TYPE,
			static::is() ? static::REWRITE_ENDPOINT : 'landing-view'
		);

		return $classes;
	}

	/**
	 * Redirect user to the Full View that led them to the gallery, rather than
	 * sending them to the post that the gallery is linked to.
	 *
	 * @param string       $close_link URL used by gallery's close button.
	 * @param string|false $referrer   Referrer parsed to determine return URL.
	 * @return string
	 */
	public function override_gallery_close_button_link(
		string $close_link,
		$referrer
	): string {
		if (empty($referrer)) {
			return $close_link;
		}

		if (
			false !== strpos($referrer, static::QUERY_VAR)
			|| preg_match(
				'#/digital-daily/([^/]+)/full-view/?#',
				$referrer
			)
		) {
			$close_link = $referrer;
		}

		return $close_link;
	}

	/**
	 * Toggle permalink filters that direct visitors to the full view.
	 *
	 * @param bool $add True to add filters, false to remove.
	 */
	public function toggle_permalink_filters(bool $add): void
	{
		foreach ($this->_permalink_filter_details['filters'] as $filter) {
			if ($add) {
				add_filter(
					$filter,
					$this->_permalink_filter_details['callback'],
					$this->_permalink_filter_details['priority'],
					2
				);
			} else {
				remove_filter(
					$filter,
					$this->_permalink_filter_details['callback'],
					$this->_permalink_filter_details['priority']
				);
			}
		}
	}

	/**
	 * Check if permalink filters are active.
	 *
	 * @return bool
	 */
	public function check_permalink_filters(): bool
	{
		foreach ($this->_permalink_filter_details['filters'] as $filter) {
			$hooked = has_filter(
				$filter,
				$this->_permalink_filter_details['callback']
			);

			if ($hooked) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Modify permalinks to direct visitors to the full view.
	 *
	 * Each post object's permalink is rewritten to point to the Digital Daily
	 * issue it appears in, along with the rewrite endpoint that triggers full
	 * view. The post object's slug is appended as a hash to jump the reader to
	 * the chosen entry.
	 *
	 * @param string      $permalink  Permalink to (potentially) be rewritten.
	 * @param WP_Post|int $post_or_id Post object or ID.
	 * @return string
	 */
	public function modify_permalinks(
		string $permalink,
		$post_or_id
	): string {
		// Ensure we're referencing the Digital Daily issue object.
		global $wp_the_query;

		// Prevent recursion when we call `get_permalink()` below.
		if (POST_TYPE === get_post_type($post_or_id)) {
			return $permalink;
		}

		$id = $post_or_id instanceof WP_Post
			? $post_or_id->ID
			: (int) $post_or_id;

		$this->toggle_permalink_filters(false);

		if (static::_should_use_pretty_permalink($id)) {
			$permalink = sprintf(
				'%1$s/%2$s#%3$s',
				untrailingslashit(
					get_permalink(
						$wp_the_query->get_queried_object_id()
					)
				),
				user_trailingslashit(static::REWRITE_ENDPOINT),
				Larva\get_id_attribute_for_post_id($id)
			);
		} else {
			$permalink = sprintf(
				'%1$s#%2$s',
				add_query_arg(
					static::QUERY_VAR,
					1,
					get_permalink(
						$wp_the_query->get_queried_object_id()
					)
				),
				Larva\get_id_attribute_for_post_id($id)
			);
		}

		$this->toggle_permalink_filters(true);

		return $permalink;
	}

	/**
	 * Toggle content filters that convert to single-column view.
	 *
	 * @param bool $add True to render as single column, false to use Core renderer.
	 */
	public function toggle_content_filters(bool $add): void
	{
		static $priority = null;

		// This will only work once, so we cache it locally.
		if (null === $priority && $add) {
			// Cannot test directly, but tested indirectly.
			$priority = has_filter('the_content', 'do_blocks'); // @codeCoverageIgnore
		}

		if ($add) {
			remove_filter('the_content', 'do_blocks', $priority);
			add_filter('the_content', [$this, 'override_blocks'], $priority);
		} else {
			add_filter('the_content', 'do_blocks', $priority);
			remove_filter('the_content', [$this, 'override_blocks'], $priority);
		}
	}

	/**
	 * Flatten block.
	 *
	 * @param string $content Post content containing un-rendered blocks.
	 * @return string
	 */
	public function override_blocks(string $content): string
	{
		$blocks = (new Block_Flattener($content)
		)->get();

		$blocks = array_filter($blocks, [$this, '_exclude_blocks']);

		if (empty($blocks)) {
			return $content;
		}

		$this->toggle_content_filters(false);

		$block_output = do_blocks(
			serialize_blocks(
				$blocks
			)
		);

		$this->toggle_content_filters(true);

		return $block_output;
	}

	/**
	 * Exclude certain blocks from full view, per designs.
	 *
	 * @param array $block Block details.
	 * @return bool
	 */
	protected function _exclude_blocks(array $block): bool
	{
		$excluded = [
			'core/separator',
			'core/spacer',
		];

		// Variable is defined as an array and never overwritten.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		return !in_array($block['blockName'], $excluded, true);
	}

	/**
	 * Determine if conditions permit a pretty permalink.
	 *
	 * @param WP_Post|int $post_or_id Post object or ID.
	 * @return bool
	 */
	protected static function _should_use_pretty_permalink(
		$post_or_id
	): bool {
		global $wp_rewrite;

		$status_supported = apply_filters(
			'pmc_digital_daily_full_view_post_status_should_use_pretty_permalink',
			'publish' === get_post_status($post_or_id),
			$post_or_id
		);

		return $wp_rewrite->using_permalinks()
			&& $status_supported
			&& !is_preview();
	}
}
