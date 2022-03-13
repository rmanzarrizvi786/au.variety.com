<?php

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Feed_Related_Links {

	use Singleton;

	protected function __construct() {
		add_action( 'pmc_custom_feed_start', array( $this, 'action_pmc_custom_feed_start' ), 10, 3 );
	}

	// action hook before feed template start
	public function action_pmc_custom_feed_start( $feed = false, $feed_options = false, $template = '' ) {

		add_action( 'pmc_custom_feed_item', array( $this, 'action_pmc_custom_feed_item' ), 10, 2 );

		if ( empty( $feed_options['related-links'] ) ) {
			return;
		}

		add_filter( 'pmc_custom_feed_post_start', array( $this, 'filter_pmc_custom_feed_post_start'), 10, 2 );

	} // function

	/**
	 * @codeCoverageIgnore Urgent fix to handle fatal, need to go out now
	 */
	public function filter_pmc_custom_feed_post_start( $post, $feed_options = false ) {

		if ( empty( $post ) || empty( $post->ID ) || !empty( $post->_related_links_processed ) ) {
			return $post;
		}

		$post->_related_links_processed = true;
		if ( !isset( $post->feed_related ) ) {
			$post->feed_related = array();
		}

		$last_n_days = apply_filters( 'pmc_custom_feed_related_links_last_n_days', 0 );

		$post_types = PMC_Custom_Feed_Helper::validate_post_types( PMC_Custom_Feed::get_instance()->get_feed_config( 'post_type' ) );
		if ( empty( $post_types ) ) {
			$post_types = array( 'post' );
		}
		// Allow additional modification of post types, beyond what the feed supports (originally for adding Variety Video Post types to Reuters)
		$post_types = apply_filters( 'pmc_custom_feed_related_links_post_types', $post_types );

		$related_posts = array();
		if ( function_exists( 'pmc_related_articles' ) ) {
			if ( empty( $last_n_days ) ) {
				$related_posts = pmc_related_articles( $post->ID, array( 'post_types' => $post_types ) );
			} else {
				$related_posts = pmc_related_articles(
					$post->ID,
					array(
						'post_types'  => $post_types,
						'last_n_days' => $last_n_days,
					)
				);
			}
		}

		if ( !empty( $related_posts ) ) {

			foreach ( $related_posts as $related_post ) {
				$guid = get_the_guid( $related_post->post_id );

				if ( empty( $guid ) || isset( $post->feed_related[ $guid ] ) ) {
					continue;
				}

				$post->feed_related[ $guid ] = PMC_Custom_Feed_Helper::get_link_detail( $related_post->post_id );

			}

		} // if ! empty related posts

		return $post;

	} // function

	public function action_pmc_custom_feed_item( $post, $feed_options = false ) {

		// output related node
		if ( !empty( $post->feed_related ) ) {
			echo '<related';
			PMC_Custom_Feed_Helper::render_attr( 'related' );
			echo '>';

			foreach ( $post->feed_related as $related ) {
				printf( '<guid isPermaLink="false">%s</guid>', PMC_Custom_Feed_Helper::esc_xml( $related['guid'] ) );
			}

			echo '</related>';
			unset( $post->feed_related );
		}

	} // function


} // class

PMC_Custom_Feed_Related_Links::get_instance();

// EOF
