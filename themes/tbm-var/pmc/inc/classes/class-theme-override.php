<?php
/**
 * PMC Core theme override setup.
 *
 * @package pmc-core-v2
 *
 * @since   2018-12-18
 */

namespace PMC\Core\Inc;

use \PMC\Global_Functions\Traits\Singleton;
use PMC\Core\Inc\Fieldmanager\Fields;

class Theme_Override {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * Initializes the theme.
	 */
	protected function __construct() {

		$this->_setup_hooks();
	}

	/**
	 * To setup actions/filters.
	 *
	 * @return void
	 * @since  2018-12-18
	 *
	 */
	protected function _setup_hooks() {

		/**
		 * Actions
		 */
		add_action( 'init', [ $this, 'disable_live_chat' ] );
		add_action( 'pre_get_posts', [ $this, 'maybe_enable_es_wp_query' ] );

		/**
		 * Filters
		 */
		add_filter( 'jetpack_open_graph_tags', [ $this, 'open_graph_tags' ] );
		add_filter( 'wp_kses_allowed_html', [ $this, 'kses_allow_lazy_src_attr' ], 10, 2 );

		add_filter( 'vip_live_chat_enabled', '__return_false' ); // disable olark.
		add_filter( 'pmc_inject_wrap_paragraph', '__return_false' );
		add_filter( 'pmc_amazon_ads_enabled', '__return_false' ); // disable render-blocking Amazon Ads.
		add_filter( 'pmc_core_random_recent_post_args', [ $this, 'modify_random_recent_post_args' ] );
		add_filter( 'pmc_core_relationship_taxonomies', [ $this, 'pmc_core_relationship_taxonomies' ] );

	}

	/**
	 * Disable 'post_tag' from PMC core taxonomies until it properly uses autocomplete.
	 *
	 * @param array $taxonomies Existing taxonomies.
	 *
	 * @return array
	 */
	public function pmc_core_relationship_taxonomies( $taxonomies ) {

		if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
			$taxonomies = array_diff( $taxonomies, [ 'post_tag' ] );
		}

		return $taxonomies;
	}

	/**
	 * Allow lazy loading attribute in wp_kses escaping.
	 */
	public function kses_allow_lazy_src_attr( $attr, $context ) {

		$attr['img']['data-lazy-src'] = 1;

		return $attr;
	}

	/**
	 * Disable Olark, it breaks up and causes our UI JS to halt
	 * Added on VY RS launch after go ahead from Pete Schiebel in Stormchat
	 *
	 * @since 2018-12-18
	 */
	public function disable_live_chat() {

		if ( function_exists( 'wpcom_vip_remove_livechat' ) ) {
			wpcom_vip_remove_livechat();
		}
	}

	/**
	 * Add a default OpenGraph image, if no other images present
	 *
	 * @param $tags
	 *
	 * @return mixed
	 */
	public function open_graph_tags( $tags ) {

		//on article page og:image should use 4:3 ratio image.
		if ( is_single() ) {

			$image_attr = wp_get_attachment_image_src(
				get_post_thumbnail_id( get_queried_object_id() ),
				'og-image',
				false
			);
			if ( ! empty( $image_attr[0] ) ) {
				$tags['og:image'] = $image_attr[0];

				return $tags;
			}
		}

		$default_url = Media::get_instance()->get_placeholder_img_url();
		if ( empty( $tags['og:image'] ) ) {
			$tags['og:image'] = $default_url;
		} else {
			$url = $tags['og:image'];
			if ( ! empty( $url[0] ) ) {
				$url = $url[0];
			}
			$domain = wp_parse_url( $url, PHP_URL_HOST );

			//If its wordpress's default image we want to change to ours.
			if ( - 1 < stripos( $domain, 'wordpress.com' ) ) {
				$tags['og:image'] = $default_url;
			}
		}

		return $tags;
	}

	/**
	 * Enable Elastic search for category, tag and author pages.
	 *
	 * @param $query
	 */
	public function maybe_enable_es_wp_query( $query ) {

		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->is_category() || $query->is_tag() || $query->is_tax( 'vertical' ) ) {
			if ( Theme::get_instance()->is_es_enabled() ) {
				$query->set( 'es', true );
			}
		}

		if ( $query->is_home() ) {
			if ( $query->is_paged() ) {
				$paged = ( $query->get( 'paged' ) ) ? $query->get( 'paged' ) : 1;
				if ( 3 < $paged ) {
					if ( Theme::get_instance()->is_es_enabled() ) {
						$query->set( 'es', true );
					}
				}
			} elseif ( \PMC::is_production() ) {
				$query->set(
					'date_query',
					[
						[
							'after' => '7 day ago',
						],
					]
				);
			}
		}

	}

	/**
	 * Enable ES and put date limit on random recent posts
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function modify_random_recent_post_args( array $args ) {

		if ( Theme::get_instance()->is_es_enabled() ) {
			$args['es'] = true;
		}

		$args['date_query'] = [
			'after' => '90 day ago',
		];

		return $args;
	}

}

//EOF
