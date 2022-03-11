<?php
/**
 * Config for PMC Event Tracking plugin
 *
 * @author  Amit Gupta <agupta@pmc.com>
 *
 * @since   2017-01-16
 * @version 2017-09-19 - Dhaval Parekh - CDWE-498 - Copied from old theme.
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;


use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Global_Functions\Utility\Device;

class PMC_Event_Tracking {

	use Singleton;

	/**
	 * Construct Method
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup listeners to WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/**
		 * Filters
		 */
		add_filter( 'pmc-event-tracking-single-post-inline-link-post-types', array( $this, 'get_post_types' ) );
		add_filter( 'pmc-event-tracking-single-post-scroll-post-types', array( $this, 'get_post_types' ) );
		add_filter( 'pmc-event-tracking-single-post-inline-link-selector', array( $this, 'get_inline_link_event_selector_for_single_posts' ) );
		add_filter( 'pmc-event-tracking-single-post-scroll-events', array( $this, 'get_scroll_events_for_single_posts' ) );
		add_filter( 'pmc_ga_event_tracking', [ $this, 'filter_single_article_events' ] );

	}

	/**
	 * Called on 'pmc-event-tracking-single-post-inline-link-post-types' and
	 * 'pmc-event-tracking-single-post-scroll-post-types' hooks, this method returns the
	 * post types on which to setup event tracking.
	 *
	 * @param  array $post_types List of post types.
	 * @return array
	 */
	public function get_post_types( $post_types = array() ) {

		return array(
			'post',
		);

	}

	/**
	 * Called on 'pmc-event-tracking-single-post-inline-link-selector' hook, this
	 * method returns sprintf() compatible template for jQuery to select links
	 * in post content.
	 *
	 * @param  string $selector
	 * @return string
	 */
	public function get_inline_link_event_selector_for_single_posts( $selector = '' ) {

		return ".l-wrap__main .c-content a[href*='%s']";

	}

	/**
	 * Called on 'pmc-event-tracking-single-post-scroll-events' hook, this method
	 * returns events payload for tracking scroll on post pages.
	 *
	 * @param  array $events
	 * @return array
	 */
	public function get_scroll_events_for_single_posts( $events = array() ) {

		if ( empty( $events ) || ! is_array( $events ) ) {
			$events = array();
		}

		return array_merge(

			$events,

			array(

				array(
					'action'         => 'inview',
					'selector'       => '.l-page__content .l-article__header h1',
					'category'       => 'article-page',
					'label'          => 'headline-view',
					'nonInteraction' => true,
				),
				array(
					'action'         => 'inview',
					'selector'       => '.l-page__content .c-content p:first',
					'category'       => 'article-page',
					'label'          => 'top-post-view',
					'nonInteraction' => true,
				),
				array(
					// :pmc-middle-child is a custom jQuery selector
					// located in pmc-js-libraries/js/pmc-jquery-extensions
					'action'         => 'inview',
					'selector'       => '.l-page__content .c-content p:pmc-middle-child',
					'category'       => 'article-page',
					'label'          => 'mid-post-view',
					'nonInteraction' => true,
				),
				array(
					'action'         => 'inview',
					'selector'       => '.l-page__content .c-content p:last',
					'category'       => 'article-page',
					'label'          => 'bottom-post-view',
					'nonInteraction' => true,
				),
				array(
					'action'         => 'inview',
					'selector'       => 'footer.l-footer',
					'category'       => 'article-page',
					'label'          => 'footer-view',
					'nonInteraction' => true,
				),
				array(
					// :pmc-middle-child is a custom jQuery selector
					// located in pmc-js-libraries/js/pmc-jquery-extensions
					'action'         => 'content-consumed',
					'selector'       => '.l-page__content .c-content p:pmc-middle-child',
					'category'       => 'article-page',
					'label'          => 'content-consumed',
					'nonInteraction' => true,
				),

			)

		);

	}

	/**
	 * Events for single articles.
	 *
	 * @param array $events An array of existing events
	 *
	 * @return array
	 */
	public function filter_single_article_events( array $events = [] ): array {

		if ( ! is_singular( [ 'post' ] ) || ! is_array( $events ) ) {
			return $events;
		}

		$tag_events = [];

		$tags = \PMC\Core\Inc\Theme::get_instance()->get_post_terms( get_the_ID() );

		if ( is_array( $tags ) && ! empty( $tags['post_tag'] ) ) {
			foreach ( $tags['post_tag'] as $post_tag ) {

				$term_link = get_term_link( $post_tag->term_id );
				$term_slug = $post_tag->slug;

				if ( Device::get_instance()->is_tablet() ) {
					$tag_event_label = '[T] tag-' . $term_slug;
				} elseif ( Device::get_instance()->is_mobile() ) {
					$tag_event_label = '[M] tag-' . $term_slug;
				} else {
					$tag_event_label = '[D] tag-' . $term_slug;
				}

				array_push(
					$tag_events,
					[
						'action'         => 'click',
						'selector'       => ".article-tags a[href*='{$term_link}']",
						'category'       => 'article-page',
						'label'          => $tag_event_label,
						'nonInteraction' => false,
					]
				);
			}
		}

		return array_merge( $tag_events, $events );
	}

} // end class


//EOF
