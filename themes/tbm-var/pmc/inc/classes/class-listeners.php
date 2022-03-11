<?php
/**
 * Class Listeners
 *
 * @package PMC\Core\Inc
 */

namespace PMC\Core\Inc;

use PMC\Global_Functions\Traits\Singleton;

class Listeners {

	use Singleton;

	protected function __construct() {

		$this->_setup_hooks();

	}

	protected function _setup_hooks() {

		// Filters
		add_filter( 'pmc_html_tag_namespaces', [ $this, 'filter_html_tag_namespaces' ], 10, 1 );
		add_filter( 'sailthru_process_recurring_post', [ $this, 'process_recurring_post_for_exacttarget' ], 10, 2 );

	}

	/**
	 * Filter the namespaces rendered in the <html> tag
	 *
	 * @param array $namespaces
	 *
	 * @return array
	 */
	public function filter_html_tag_namespaces( $namespaces = [] ) {

		// Inform the browser how to parse open graph <og> tags and attributes.
		$namespaces['xmlns:og'] = [
			'value'           => 'http://ogp.me/ns#',
			'escape_callback' => 'esc_url',
		];

		// Inform the browser how to parse Facebook <fb> tags and attributes.
		$namespaces['xmlns:fb'] = [
			'value'           => 'http://www.facebook.com/2008/fbml',
			'escape_callback' => 'esc_url',
		];

		return $namespaces;
	}

	/**
	 * Add vertical taxonomy support on Exact Target feeds
	 *
	 * @since   2015-07-15
	 * @uses    sailthru_process_recurring_post filter from pmc-exacttarget
	 *
	 *
	 * @version 2015-07-15 Amit Sannad - PPT-5174
	 *
	 * @param $feed_post
	 * @param $original_post
	 *
	 * @return array
	 */
	public function process_recurring_post_for_exacttarget( $feed_post, $original_post ) {

		if ( ! is_feed() ) {
			return;
		}
		$primary_vertical = array();
		$vertical         = array();

		if ( taxonomy_exists( 'vertical' ) ) {
			$p_vertical = pmc_get_the_primary_term( 'vertical', $original_post->ID );
			if ( ! empty( $p_vertical ) ) {
				$primary_vertical = array(
					'name' => isset( $p_vertical->name ) ? $p_vertical->name : '',
					'link' => isset( $p_vertical->link ) ? $p_vertical->link : '',
				);
			}

			$all_verticals = get_the_terms( $original_post, 'vertical' );

			if ( ! empty( $all_verticals ) && ! is_wp_error( $all_verticals ) ) {
				foreach ( $all_verticals as $vert ) {
					$vertical[] = array(
						'name' => isset( $vert->name ) ? $vert->name : '',
						'link' => isset( $vert->link ) ? $vert->link : '',
					);
				}
			}
		}
		$feed_post['primary_vertical'] = $primary_vertical;
		$feed_post['verticals']        = $vertical;

		return $feed_post;
	}

}

// EOF
