<?php
/**
 * Carousel
 *
 * Responsible for carousel related functionality.
 *
 * @package pmc-variety
 */

namespace Variety\Plugins\Variety_VIP;

use \PMC\Global_Functions\Traits\Singleton;
use Variety\Inc\Carousels;

/**
 * Class Carousel
 */
class Carousel {

	use Singleton;

	/**
	 * Fetch VIP Carousel
	 *
	 * @param string      $carousel The carousel name.
	 * @param int         $count Number of items to fetch.
	 * @param bool|string $taxonomy The taxonomy of the carousel.
	 * @param null|array  $filler_post_types Posts types to fill posts from.
	 * @return array
	 *
	 * @codeCoverageIgnore
	 */
	public static function get_vip_carousel_posts( $carousel, $count, $taxonomy = false, $filler_post_types = null ) {

		if ( empty( $filler_post_types ) ) {
			$filler_post_types = Content::VIP_POST_TYPE;
		}

		// Add filters.
		$post_type_filter = function( $args ) use ( $filler_post_types ) {
			$args['post_type'] = $filler_post_types;
			return $args;
		};

		add_filter( 'pmc_carousel_items_fallback_args', $post_type_filter );
		add_filter( 'pmc_carousel_items_fallback_latest_args', $post_type_filter );

		$_posts = Carousels::get_carousel_posts( $carousel, $count, $taxonomy, 'post' );

		// Remove filters.
		remove_filter( 'pmc_carousel_items_fallback_args', $post_type_filter );
		remove_filter( 'pmc_carousel_items_fallback_latest_args', $post_type_filter );

		return $_posts;

	}

}

// EOF.
