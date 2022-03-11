<?php

/**
 * PMC_Spark theme setup.
 *
 * @package pmc-variety
 *
 * @since   2018-12-18
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

class Media
{

	use Singleton;

	/**
	 * @codeCoverageIgnore
	 * Class constructor.
	 */
	protected function __construct()
	{

		/**
		 * Actions
		 */
		add_action('after_setup_theme', [$this, 'setup']);

		/**
		 * Filters
		 */
		add_filter('wpcom_thumbnail_editor_args', [$this, 'thumbnail_editor_args']);
		add_filter('pmc_core_placeholder_img_url', [$this, 'change_placeholder_image']);
		add_filter('wp_calculate_image_sizes', [$this, 'image_sizes_attr'], 10, 2);
		add_filter('wp_calculate_image_srcset_meta', [$this, 'calculate_image_srcset_meta'], 10, 5);
		add_action('pre_get_posts', [$this, 'filter_attachments_in_admin']);
	}

	/**
	 * @codeCoverageIgnore
	 * Define image sizes and other theme setup chores.
	 */
	public function setup()
	{

		add_theme_support('post-thumbnails');

		// 16:9
		add_image_size('landscape-xxxlarge', 1360, 765, ['center', 'top']);
		add_image_size('landscape-xxlarge', 1000, 563, ['center', 'top']);
		add_image_size('landscape-xlarge', 910, 511, ['center', 'top']);
		add_image_size('landscape-large', 681, 383, ['center', 'top']);
		add_image_size('landscape-medium', 450, 253, ['center', 'top']);
		add_image_size('landscape-small', 250, 140, ['center', 'top']);
		add_image_size('variety-top-stories-1', 296, 166, true);
		add_image_size('variety-top-stories-2', 320, 230, true);

		// 3:2
		add_image_size('yahoo-thumb', 630, 420, true);

		// 1:1
		add_image_size('square-medium', 400, 400, true);
		add_image_size('square-small', 225, 225, true);
		add_image_size('variety-popular', 65, 65, true);
	}

	/**
	 * Crop ratio defined here
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function thumbnail_editor_args($args)
	{

		$args['image_ratio_map'] = [
			'1:1'  => [
				'guest-author-128',
				'guest-author-96',
				'guest-author-64',
				'guest-author-50',
				'guest-author-32',
				'square-medium',
				'square-small',
				'variety-popular',
			],
			'3:2'  => [
				'flv-shortcode-image',
				'yahoo-thumb',
			],
			'4:3'  => [
				'related-articles',
				'og-image',
			],
			'16:9' => [
				'mmc_newsletter_featured',
				'mmc_newsletter_thumb',
				'carousel-small-thumb',
				'flv-shortcode-image-lrg',
				'landscape-xxlarge',
				'landscape-xlarge',
				'landscape-large',
				'landscape-medium',
				'landscape-small',
				'variety-top-stories-1',
				'variety-top-stories-2',
			],
		];

		return $args;
	}

	/**
	 * @codeCoverageIgnore
	 * Add custom image sizes attribute to enhance responsive image functionality
	 * for content images
	 *
	 * @param string $sizes A source size value for use in a 'sizes' attribute.
	 * @param array  $size  Image size. Accepts an array of width and height
	 *                      values in pixels (in that order).
	 * @return string A source size value for use in a content image 'sizes' attribute.
	 */
	public function image_sizes_attr($sizes, $size)
	{

		return '(min-width: 87.5rem) 1000px, (min-width: 78.75rem) 681px, (min-width: 48rem) 450px, (max-width: 48rem) 250px';
	}

	/**
	 * Pre-filter the image meta to be able to reduce sizes.
	 *
	 * @param array  $image_meta    The image meta data as returned by 'wp_get_attachment_metadata()'.
	 * @param array  $size_array    Array of width and height values in pixels (in that order).
	 * @param string $image_src     The 'src' of the image.
	 * @param int    $attachment_id The image attachment ID or 0 if not supplied.
	 *
	 * @return mixed
	 */
	public function calculate_image_srcset_meta($image_meta, $size_array, $image_src, $attachment_id)
	{

		$sizes = [
			'medium'               => '',
			'thumbnail'            => '',
			'carousel-small-thumb' => '',
			'landscape-large'      => '',
			'landscape-medium'     => '',
			'landscape-small'      => '',
		];

		if (!empty($image_meta['sizes']) && is_array($image_meta['sizes'])) {
			$image_meta['sizes'] = array_intersect_key($image_meta['sizes'], ($sizes));
		}

		return $image_meta;
	}

	/**
	 * Change placeholder image for the theme
	 * @return string
	 */
	public function change_placeholder_image()
	{

		return CHILD_THEME_URL . '/assets/public/lazyload-fallback.gif';
	}

	/**
	 * @codeCoverageIgnore
	 * Filter images shows in the edit post media uploader
	 *
	 * @param WP_Query $query The current query in progress
	 *
	 * @return WP_Query The *possibly* modified query
	 * @since  5/9/2016
	 *
	 * @see    PMCVIP-1572
	 *
	 * @author James Mehorter <james.mehorter@pmc.com>
	 */
	function filter_attachments_in_admin($query)
	{

		global $pagenow;

		// Only proceed if we're in the admin
		if (!$query->is_admin) {
			return $query;
		}

		// Only proceed when we're querying a post type
		if (empty($query->query_vars['post_type'])) {
			return $query;
		}

		// Only proceed if we're querying attachment posts
		if ('attachment' !== $query->query_vars['post_type']) {
			return $query;
		}

		// Only proceed if attachments are being queried
		if (
			('admin-ajax.php' === $pagenow && isset($_POST['action']) && 'query-attachments' === $_POST['action']) // phpcs:ignore
			|| 'upload.php' === $pagenow
		) {
			// Add a filter to the posts where clause to restrict the selected attachments
			add_filter('posts_where', [$this, 'filter_attachment_by_date']);
		}

		return $query;
	}

	/**
	 * @codeCoverageIgnore
	 * Where clause to filter images older then 2013-02-19
	 *
	 * @param string $where The current query's SQL where clause
	 *
	 * @return string The *possibly* modified where clause
	 * @since  5/9/2016
	 *
	 * @see    PMCVIP-1572
	 *
	 * @author James Mehorter <james.mehorter@pmc.com>
	 */
	public function filter_attachment_by_date($where = '')
	{

		// Only proceed if we're in the admin
		if (!is_admin()) {
			return $where;
		}

		// Only proceed if the current user is a member of the getty archive group
		/* if ( pmc_current_user_is_member_of( 'pmc-getty-access' ) ) {
			return $where;
		} */

		// If non of the above conditions halted processing,
		// only fetch images which are newer/equal than/to Jan 01, 2016
		$where .= " AND post_date >= '2016-01-01'";

		return $where;
	}
}
//EOF
