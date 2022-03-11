<?php
/**
 * Configuration class for pmc-seo-tweaks plugin.
 *
 * @author Jignesh Nakrani <jignesh.nakrani@rtcamp.com>
 *
 * @since 2018-06-05 READS-1097
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Seo_Tweaks {

	use Singleton;

	/**
	 * Function to initialize actions or filters.
	 *
	 */
	protected function __construct() {

		add_filter( 'pmc_seo_tweaks_robots_override', [ $this, 'filter_pmc_seo_tweaks_robots_override' ] );
		add_filter( 'pmc_canonical_overrides_post_types', [ $this, 'filter_pmc_canonical_override_post_types' ] );
		add_filter( 'pmc_canonical_url', [ $this, 'filter_pmc_canonical_url' ], 11 );
		add_filter( 'pmc_prev_next_rel_tag_url', [ $this, 'filter_pmc_prev_next_rel_tag_url' ] );
		add_filter( 'pmc_seo_tweaks_robot_names', [ $this, 'filter_pmc_seo_tweaks_robot_names' ] );
		remove_filter( 'amt_metatags', 'pmc_amt_metatags', 999 );
		add_filter( 'amt_meta_keywords', '__return_null' );
		add_filter( 'amt_metatags', [ $this, 'remove_keywords' ] );
		add_filter( 'pmc_seo_add_meta_tags', [ $this, 'remove_keywords_tag' ] );
	}

	/**
	 * Function to override seo tweak plugin robots meta tag content.
	 *
	 * @param  string $meta_value Robots meta value.
	 *
	 * @version 2018-06-08 Kelin Chauhan <kelin.chauhan@rtcamp.com> READS-1290
	 *
	 * @return bool|string
	 */
	public function filter_pmc_seo_tweaks_robots_override( $meta_value ) {

		if ( is_singular( 'pmc-gallery' ) ) {
			return 'index, follow';
		}

		if ( $this->special_case() ) {
			return 'noindex, follow';
		}

		return $meta_value;
	}

	/**
	 * Function to override seo tweak plugin canonical url.
	 *
	 * @param  array $post_types already registered post types.
	 *
	 * @version 2020-12-06 Dan Berko BR-964
	 *
	 * @return array
	 */
	public function filter_pmc_canonical_override_post_types( $post_types ) {

		$post_types[] = 'variety_top_video';

		return $post_types;
	}

	/**
	 * Removes canonical url tag if 'noindex, follow' added for page.
	 * The canonical tag is unnecessary and will contradict the 'noindex' rule if both are allowed on the same page
	 *
	 * @param $canonical_url string canonical URL for the current page
	 *
	 * @version 2018-06-08 Kelin Chauhan <kelin.chauhan@rtcamp.com> READS-1290
	 *
	 * @return bool|string
	 */
	public function filter_pmc_canonical_url( $canonical_url ) {

		if ( $this->special_case() ) {
			return false;
		}

		if ( is_single() ) {

			$canonical = get_post_meta( get_queried_object_id(), 'dirt_permalink', true );

			if ( ! empty( $canonical ) ) {
				return esc_url( $canonical );
			}
		}

		return $canonical_url;
	}

	/**
	 * Filter rel=prev/next URL for custom post types/taxonomies archive
	 *
	 * @param $url string URL for the current page
	 *
	 * @version 2018-07-19 Jignesh Nakrani <jignesh.nakrani@rtcamp.com> READS-1082
	 *
	 * @return bool|string
	 */
	public function filter_pmc_prev_next_rel_tag_url( $url ) {

		if ( is_post_type_archive( \PMC\Gallery\Defaults::NAME ) ) {

			$url = home_url( 'galleries/' );

		} elseif ( is_tax( '_post-options', 'has-video' ) ) {

			$url = '';

		} elseif ( is_post_type_archive( 'issue' ) ) {

			$url = home_url( 'issues/' );

		}

		return $url;
	}

	/**
	 * Function to override seo tweak plugin robot names.
	 *
	 * @param array $robot_names robot names.
	 *
	 * @return array
	 */
	public function filter_pmc_seo_tweaks_robot_names( $robot_names ) {

		if ( is_author() ) {

			$find_key = array_search( 'robots', (array) $robot_names, true );

			unset( $robot_names[ $find_key ] );
			$robot_names = array_values( $robot_names );
		}

		if ( is_singular( 'pmc-gallery' ) ) {
			$robot_names = [ 'googlebot' ];
		}

		return $robot_names;

	}

	/**
	 * Special case for SEO
	 *
	 * @return bool
	 */
	public function special_case() {

		if ( is_tag() || is_tax( [ 'variety_vip_tag', 'print-issues', 'editorial' ] ) ) {
			return true;
		}

		if ( ( is_home() || is_category() || is_tax() ) && is_paged() ) {
			return true;
		}

		if ( 'on' === get_post_meta( get_the_ID(), '_pmc_seo_archive', true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $meta_tags
	 *
	 * @return string|string[]|null
	 */
	public function remove_keywords( $meta_tags = '' ) {

		$meta_tags = preg_replace( '/<meta name="keywords" [^>]+\>/', '', $meta_tags );

		return $meta_tags;

	}

	/**
	 * @param array $meta_tags
	 *
	 * @return array
	 */
	public function remove_keywords_tag( $meta_tags = [] ) {

		unset( $meta_tags['keywords'] );

		return $meta_tags;
	}

}
