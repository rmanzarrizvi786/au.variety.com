<?php
/**
 * Event tracking service for Single Post pages
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since 2017-01-13
 */

namespace PMC\Event_Tracking\Service;

use \PMC\Global_Functions\Traits\Singleton;
use \CheezCapDropdownOption;


class Single_Post {

	use Singleton;

	const ID = 'pmc_et_single';

	/**
	 * Class initialization
	 *
	 * @return void
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Method to setup listeners to hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/*
		 * Actions
		 */
		add_action( 'wp', array( $this, 'maybe_setup_tracking' ) );	//we want to hook in late so that WP_Query vars etc are available

		/*
		 * Filters
		 */
		add_filter( 'pmc_global_cheezcap_options', array( $this, 'add_cheezcap_options' ) );

	}

	/**
	 * Conditional method to determine if scroll tracking should be setup or not
	 *
	 * @return boolean Returns TRUE if scroll tracking should be setup else FALSE
	 */
	protected function _should_setup_scroll_tracking() {

		$post_types = apply_filters( 'pmc-event-tracking-single-post-scroll-post-types', array( 'post' ) );
		$cheezcap_option = get_option( sprintf( 'cap_%s_enable_scroll_tracking', self::ID ), 'disabled' );

		if ( is_singular( $post_types ) && $cheezcap_option === 'enabled' ) {
			return true;
		}

		return false;

	}

	/**
	 * Conditional method to determine if inline link tracking should be setup or not
	 *
	 * @return boolean Returns TRUE if inline link tracking should be setup else FALSE
	 */
	protected function _should_setup_inline_link_tracking() {

		$post_types = apply_filters( 'pmc-event-tracking-single-post-inline-link-post-types', array( 'post' ) );
		$cheezcap_option = get_option( sprintf( 'cap_%s_enable_inline_link_tracking', self::ID ), 'disabled' );

		if ( is_singular( $post_types ) && $cheezcap_option === 'enabled' ) {
			return true;
		}

		return false;

	}

	/**
	 * Called on 'wp' action, this method sets up different kinds of tracking depending on which types have been enabled
	 *
	 * @return void
	 */
	public function maybe_setup_tracking() {

		if ( $this->_should_setup_scroll_tracking() ) {
			add_filter( 'pmc_ga_event_tracking', array( $this, 'get_scroll_events' ) );
		}

		if ( $this->_should_setup_inline_link_tracking() ) {
			add_filter( 'pmc_ga_event_tracking', array( $this, 'get_inline_link_events' ) );
		}

	}

	/**
	 * Method to add CheezCap options for enabling/disabling different types of tracking
	 *
	 * @param array $cheezcap_options Array of CheezCap option objects
	 * @return array
	 */
	public function add_cheezcap_options( $cheezcap_options = array() ) {

		if ( ! is_array( $cheezcap_options ) ) {
			$cheezcap_options = array();
		}

		$cheezcap_options[] = new CheezCapDropdownOption(
			__( 'GA - Enable Article Scroll Tracking', 'pmc-event-tracking' ),
			__( 'When enabled, GA events are sent as users scroll through an article. See CDWE-105.', 'pmc-event-tracking' ),
			sprintf( '%s_enable_scroll_tracking', self::ID ),
			array(
				'disabled',
				'enabled',
			),
			0,
			array( __( 'Disabled', 'pmc-event-tracking' ), __( 'Enabled', 'pmc-event-tracking' ) )
		);

		$cheezcap_options[] = new CheezCapDropdownOption(
			__( 'GA - Enable Article Inline Link Tracking', 'pmc-event-tracking' ),
			__( 'When enabled, GA events are sent for clicks on links in article body. See CDWE-105.', 'pmc-event-tracking' ),
			sprintf( '%s_enable_inline_link_tracking', self::ID ),
			array(
				'disabled',
				'enabled',
			),
			0,
			array( __( 'Disabled', 'pmc-event-tracking' ), __( 'Enabled', 'pmc-event-tracking' ) )
		);

		return $cheezcap_options;

	}

	/**
	 * Method which returns an array of scroll events which need to be tracked
	 *
	 * @param array $events
	 * @return array
	 */
	public function get_scroll_events( $events = array() ) {

		if ( ! is_array( $events ) ) {
			$events = array();
		}

		$events_to_track = apply_filters( 'pmc-event-tracking-single-post-scroll-events', $events );

		if ( empty( $events_to_track ) ) {
			$events_to_track = $events;
		}

		return $events_to_track;

	}

	/**
	 * Method which returns an array of inline link events which need to be tracked
	 *
	 * @param array $events
	 * @return array
	 */
	public function get_inline_link_events( $events = array() ) {

		$link_selector = apply_filters( 'pmc-event-tracking-single-post-inline-link-selector', '' );

		if ( empty( $link_selector ) || intval( strpos( $link_selector, '%s' ) ) <= 1 ) {
			return $events;
		}

		$current_post = get_post();

		if (
			empty( $current_post ) || ! is_a( $current_post, 'WP_Post' )
			|| empty( $current_post->post_content )
		) {
			return $events;
		}

		// Get all URLs in the post content
		$urls = wp_extract_urls( $current_post->post_content );

		if ( empty( $urls ) || ! is_array( $urls ) ) {
			return $events;
		}

		if ( ! is_array( $events ) ) {
			$events = array();
		}

		$blocklist_extensions = array(
			'gif',
			'jpg',
			'jpeg',
			'png',
			'tiff',
			'svg',
			'bmp',
		);

		$url_count = count( $urls );
		$link_events = array();

		for ( $i = 0; $i < $url_count; $i++ ) {

			$url_parts = wp_parse_url( $urls[ $i ] );

			if ( empty( $url_parts ) || ! is_array( $url_parts ) ) {
				// bad URL, move on to next one
				continue;
			}

			if ( ! isset( $url_parts['path'] ) ) {
				$url_parts['path'] = '';
			}

			$url_parts = array_map( 'untrailingslashit', $url_parts );

			$maybe_url_extension = strtolower( pathinfo( $url_parts['path'], PATHINFO_EXTENSION ) );

			if ( in_array( $maybe_url_extension, (array) $blocklist_extensions, true ) ) {
				// not something we want to track,
				// move on to next one
				continue;
			}

			$url_selector = esc_url( $url_parts['host'] . $url_parts['path'] );

			$link_events[] = array(

				'action'         => 'click',
				'selector'       => sprintf( $link_selector, $url_selector ),
				'category'       => 'inline_hyperlink',
				'label'          => sprintf( '%s_link%d_%s', $current_post->ID, ( $i + 1 ), esc_url( $urls[ $i ] ) ),
				'nonInteraction' => false,

			);

			unset( $url_selector, $maybe_url_extension, $url_parts );

		}

		return array_merge( $events, $link_events );

	}

}	//end class


//EOF
