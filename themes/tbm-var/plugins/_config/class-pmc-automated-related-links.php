<?php
/**
 * Configuration for PMC Automated related links
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;
use Variety\Inc\Related;
use \PMC_Custom_Feed;

/**
 * Class PMC_Automated_Related_Links
 */
class PMC_Automated_Related_Links {

	use Singleton;

	/**
	 * Construct Method.
	 *
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * To setup actions/filters.
	 *
	 *
	 *
	 * @return void
	 */
	protected function _setup_hooks() {
		add_filter( 'pmc_automated_related_links_options', [ $this, 'get_related_links_options' ] );
		add_filter( 'pmc_automated_related_links_linked_data_override', [ $this, 'override_linked_data' ] );

		add_filter( 'pmc_automated_related_links_template', [ $this, 'get_related_link_template' ] );
		add_filter( 'pmc_automated_related_links_filter_related_articles', [ $this, 'get_related_articles' ] );
	}

	/**
	 * To setup default option for automated related link.
	 * To allow only two related links
	 *
	 * @param array $options Options.
	 *
	 * @return array Options.
	 */
	public function get_related_links_options( $options ) {
		$options         = ( ! empty( $options ) && is_array( $options ) ) ? $options : [];
		$number_of_links = 2;

		if ( class_exists( 'PMC_Custom_Feed' ) && PMC_Custom_Feed::get_instance()->is_feed() ) {
			$number_of_links = 3;
		}

		return array_merge(
			$options,
			[
				'number_of_links' => $number_of_links,
			]
		);
	}

	/**
	 * To override linked data. SADE-213.
	 *
	 * @param array $options Options.
	 *
	 * @return array Options.
	 */
	public function override_linked_data( $linked_data ) {

		if ( empty( $linked_data ) || ! isset( $linked_data['data'] ) || ! is_array( $linked_data['data'] ) ) {
			return $linked_data;
		}

		if ( class_exists( 'PMC_Custom_Feed' ) && PMC_Custom_Feed::get_instance()->is_feed() ) {
			$count = count( $linked_data['data'] );

			if ( $count < 3 ) {

				$count = absint( $count - 3 );
				for ( $i = 0; $i < $count; $i++ ) {
					$link['url']       = '';
					$link['id']        = '';
					$link['title']     = '';
					$link['automated'] = true;

					$linked_data['data'][] = $link;
				}
			}
		}

		return $linked_data;
	}

	/**
	 * To get template for related link module for single article page.
	 *
	 * @param string $template Template for related link module.
	 *
	 * @return string Template for related link module.
	 */
	public function get_related_link_template( $template ) {

		return apply_filters( 'variety_related_posts_template', sprintf( '%s/template-parts/article/related.php', untrailingslashit( CHILD_THEME_PATH ) ) );

	}

	/**
	 * To get related links
	 *
	 * @param array $items Related links
	 *
	 * @return array Related links
	 */
	public function get_related_articles( $items ) {

		// If this is a feature, bail.
		if ( variety_is_feature() ) {
			return [];
		}

		global $post;

		// If it has the legacy shortcode in the content, bail.
		if ( has_shortcode( $post->post_content, 'pmc-related' ) ) {
			return [];
		}

		// If there's an existing "related story container" in the content, bail.
		if ( false !== strpos( $post->post_content, '<div class="related-story-container">' ) ) {
			return [];
		}

		$automated_items = [];

		if ( has_shortcode( $post->post_content, 'pmc-related-link' ) ) {
			$automated_items = PMC_Related_Link::get_instance()->get_related_items( $post->post_content );
			$automated_items = ( ! empty( $automated_items['posts'] ) && is_array( $automated_items['posts'] ) ) ? $automated_items['posts'] : [];
		}

		if ( empty( $automated_items ) || ! is_array( $automated_items ) ) {
			$automated_items = Related::get_instance()->get_related_items();
			$automated_items = ( ! empty( $automated_items['posts'] ) && is_array( $automated_items['posts'] ) ) ? $automated_items['posts'] : [];
		}

		if ( ! empty( $automated_items ) && is_array( $automated_items ) ) {

			foreach ( $items as $index => $item ) {

				if ( ! empty( $item['automated'] ) && true === $item['automated'] ) {
					$automated_item = array_pop( $automated_items );

					$items[ $index ]['id']    = $automated_item->ID;
					$items[ $index ]['title'] = $automated_item->post_title;
					$items[ $index ]['url']   = get_permalink( $automated_item->ID );

				}

			}
		}

		// If is a Dirt article.
		if ( variety_is_dirt( $post ) ) {
			$items['type'] = 'dirt';
		} elseif ( variety_is_review( $post ) ) {
			// This is singular on purpose, so it can be passed as a CSS class.
			$items['type'] = 'review';
		}

		return $items;
	}

}
