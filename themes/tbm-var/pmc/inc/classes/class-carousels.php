<?php

/**
 * PMC Core Carousels
 *
 * Class for dealing with Carousel data.
 *
 * @package pmc-core
 * @since   2019-08-26
 */

namespace PMC\Core\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Carousels.
 *
 * @since 2019.08.26
 */
class Carousels
{

	use Singleton;

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
}
