<?php

namespace PMC\Amzn_Onsite;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Custom_Feed;
use \PMC_Cheezcap;

/**
 * Admin class for PMC Amazon Onsite
 *
 * @since 2019-07-11 - Keanan Koppenhaver
 */

class Admin {
	use Singleton;

	/**
	 * Construct
	 */
	protected function __construct() {

		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
			add_action( 'save_post', [ $this, 'add_amazon_pub_date' ], 99 );
			add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );

			add_filter( 'display_post_states', [ $this, 'add_post_state' ], 10, 2 );
		} else {
			add_filter( 'pmc_custom_feed_amazon_deals_introtext', [ $this, 'filter_intro_text' ], 10, 2 );
			add_filter( 'pmc_custom_feed_amazon_deals_content_encoded', [ $this, 'get_custom_content' ], 10, 2 );
			add_filter( 'pmc_custom_feed_amazon_deals_publish_date', [ $this, 'filter_amazon_pub_date' ] );
			add_filter( 'pmc_custom_feed_posts_filter', [ $this, 'filter_amazon_feed_args' ], 10, 2 );
			add_filter( 'pmc_feed_get_posts', [ $this, 'should_bypass_sort' ], 10, 3 );
		}

		//Need to be in here as this breaks inside is_admin
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
		add_filter( 'pmc_cheezcap_groups', array( $this, 'filter_pmc_cheezcap_groups' ) );
	}

	/**
	 * Creates the Amazon Onsite menu and submenu pages
	 *
	 * @since 2019-07-11
	 *
	 */
	public function add_admin_menu() : void {

		$add_admin_menu = apply_filters( 'pmc_amzn_onsite_add_admin_menu', true );

		if ( ! empty( $add_admin_menu ) ) {
			add_menu_page(
				__( 'Amazon Onsite', 'pmc-amzn-onsite' ),
				__( 'Amazon Onsite', 'pmc-amzn-onsite' ),
				'manage_options',
				'amazon-onsite.php',
				[ $this, 'display_admin_page' ],
				\PMC::render_template( plugin_dir_path( AMZN_ONSITE_PLUGIN_FILE ) . '/icon.svg' )
			);
		}

	}

	/**
	 * Handles the display of the Amazon Onsite menu page
	 *
	 * @since 2019-07-11
	 *
	 */
	public function display_admin_page() {
		\PMC::render_template( plugin_dir_path( AMZN_ONSITE_PLUGIN_FILE ) . '/templates/onsite-admin-page.php', [], true );
	}

	/**
	 * Adds 'Amazon Onsite' as a potential post state for displaying on the post list page
	 *
	 * @since 2019-07-11
	 *
	 * @param array $post_states Current list of post states
	 * @param object $post The current post
	 *
	 * @return array $post_states The list of posts states with ours now added
	 *
	 */
	public function add_post_state( $post_states, $post ) {
		$current_screen = get_current_screen();

		if ( ! empty( $current_screen ) && 'toplevel_page_amazon-onsite' === $current_screen->base && has_term( 'amazon-associates', 'editorial', $post ) ) {
			$post_states[] = __( 'Amazon Onsite', 'pmc-amzn-onsite' );
		}

		return $post_states;
	}

	/**
	 * Enqueues the styles that we need for our plugin
	 *
	 * @since 2019-07-11
	 *
	 */
	public function enqueue_admin_styles() {
		wp_enqueue_style( 'amzn-onsite-styles', plugins_url( '/style.css', AMZN_ONSITE_PLUGIN_FILE ) );
	}

	/**
	 * Filters the intro text in the Amazon feed to allow it to be overriden by our custom Fieldmanager field
	 *
	 * @since 2019-07-11
	 *
	 * @param string $text Current intro text
	 *
	 * @return string $text Intro text after potentially overriding with our custom text
	 *
	 */
	public function filter_intro_text( $text, $post_id ) {
		$amazon_intro_text = get_post_meta( $post_id, 'amazon_intro_text', true );

		if ( ! empty( $amazon_intro_text ) ) {
			$text = $amazon_intro_text;
		}

		return $text;
	}

	/**
	 * Filters the post content in the Amazon feed to allow it to be overriden by our custom Fieldmanager field
	 *
	 * @since 2019-07-11
	 *
	 * @param string $text Current post content
	 *
	 * @return string $text Post content after potentially overriding with our custom text
	 *
	 */
	public function get_custom_content( $text, $post_id ) {
		$amazon_post_content = get_post_meta( $post_id, 'amazon_post_content', true );

		if ( ! empty( $amazon_post_content ) ) {
			return $amazon_post_content;
		}

		return $text;
	}

	/**
	 * Filters the post publish date in the Amazon feed to allow it to be overriden by our custom Fieldmanager field
	 *
	 * @since 2019-07-11
	 *
	 * @param string $text Current post date
	 *
	 * @return string $text Post date after potentially overriding with our custom date
	 *
	 */
	public function filter_amazon_pub_date( $text ) {

		$post = get_post();

		if ( empty( $post ) ) {
			// @codeCoverageIgnoreStart
			return $text;
			// @codeCoverageIgnoreEnd
		}

		$amazon_date = (int) get_post_meta( $post->ID, 'amazon_date', true );

		if ( 0 < $amazon_date ) {
			$amazon_date = $amazon_date - ( (int) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
			$text        = date_i18n( 'D, d M Y H:i:s +0000', $amazon_date );
		}

		return $text;
	}

	/**
	 * If there is no amazon date set, but a post will go out in the amazon feed,
	 * add an amazon publish date of the current date and time
	 *
	 * @since 2019-07-11
	 *
	 * @param int $post_id Post ID of the post with the given set of products
	 *
	 * @return void
	 *
	 */
	public function add_amazon_pub_date( $post_id ) {
		// If this is just a revision, bail.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$nonce = \PMC::filter_input( INPUT_POST, 'fieldmanager-amazon-onsite-info-nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $nonce, 'fieldmanager-save-amazon-onsite-info' ) || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$amazon_date = get_post_meta( $post_id, 'amazon_date', true );

		if ( has_term( 'amazon-associates', 'editorial', $post_id ) && ( empty( $amazon_date ) || intval( $amazon_date ) < 1 ) ) {
			update_post_meta( $post_id, 'amazon_date', current_time( 'timestamp', 0 ) );
		}
	}

	/**
	 * Modifies the query for the post list table inside the admin to allow
	 * editorial to do necessary sorting and filtering of Amazon Onsite posts
	 *
	 * @since 2019-07-11
	 *
	 * @param object $query The current query object
	 *
	 * @return object $query The query, after any modifications that we need to make
	 *
	 */
	public function pre_get_posts( $query ) {
		global $pagenow;

		$onsite_include = \PMC::filter_input( INPUT_GET, 'amazon_onsite', FILTER_SANITIZE_STRING );
		$orderby        = \PMC::filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );
		$order          = \PMC::filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING );

		// Bail out if not edit and not in post edit
		if ( is_admin() && 'admin.php' === $pagenow && 'amazon-onsite.php' === get_query_var( 'page' ) ) {
			// We're filtering either all onsite posts or excluding all onsite posts
			if ( ! empty( $onsite_include ) ) {
				$taxquery = [
					[
						'taxonomy' => 'editorial',
						'field'    => 'slug',
						'terms'    => 'amazon-associates',
					],
				];

				if ( 'true' === $onsite_include ) {
					$taxquery[0]['operator'] = 'IN';
					$query->set( 'tax_query', $taxquery );
				} else {
					$taxquery[0]['operator'] = 'NOT IN';
					$query->set( 'tax_query', $taxquery );
				}
			}

			if ( 'amzn_date' === $orderby ) {
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'meta_key', 'amazon_date' );
				$query->set( 'order', $order );
			}
		}
	}

	/**
	 * Modifies the posts in the Amazon Onsite feed to be sorted chronologically by amazon date
	 *
	 * @since 2019-07-11
	 *
	 * @param array $args The current query arguments a post
	 * @param array $config The configuration details of the current feed
	 *
	 * @return array $args Modified arguments for the amazon feed query
	 *
	 */
	public function filter_amazon_feed_args( $args, $config ) {
		if ( 'feed-amazon-deals' === $config['template'] ) {
			$new_args = [
				'orderby'  => 'meta_value_num',
				'meta_key' => 'amazon_date',
				'order'    => 'DESC',
			]; // WPCS: slow query ok.

			$args = array_merge( $args, $new_args );
		}

		return $args;
	}

	/**
	 * Filter to bypass the hardcoded post_date sort in all feeds, because
	 * we want to sort by amazon publish date in our Amazon Onsite feed
	 *
	 * @param $posts
	 * @param $original_posts
	 * @param $args The current query arguments a post
	 *
	 * @return Unsorted posts for amazon feeds
	 */
	public function should_bypass_sort( $posts, $original_posts, $args ) {

		if ( isset( $args['meta_key'] ) && 'amazon_date' === $args['meta_key'] ) {
			return $original_posts;
		}

		return $posts;
	}

	/**
	 * Added a cheezcap to enable the plugin
	 *
	 * @param array $cheezcap_groups List of cheezcap options.
	 *
	 * @return array $cheezcap_groups
	 */
	public function filter_pmc_cheezcap_groups( array $cheezcap_groups = [] ) : array {

		// Add an 'Amazon Onsites' cheezcap group
		$cheezcap_groups[] = new \CheezCapGroup(
			__( 'Amazon Onsites', 'pmc-amzn-onsite' ),
			'pmc-amzn-onsite',
			[
				new \CheezCapTextOption(
					__( 'Disclaimer Text', 'pmc-amzn-onsite' ),
					__( 'Disclaimer copy for why readers should trust the brand and product recommendations', 'pmc-amzn-onsite' ),
					'pmc-amzn-onsite-disclaimer-copy',
					'',
					true
				),
				new \CheezCapMultipleCheckboxesOption(
					__( 'Display Disclaimer Text', 'pmc-amzn-onsite' ),
					__( 'Display disclaimer text on site and/or Amazon Onsite feeds', 'pmc-amzn-onsite' ),
					'pmc-amzn-onsite-disclaimer-display',
					[ 'site', 'feed' ],
					[ 'Show on Site', 'Show in Amazon Onsite Feed' ],
					'', // No default-selection checkboxes, pls
					array( 'PMC_Cheezcap', 'sanitize_cheezcap_checkboxes' )
				),
			]
		);

		return $cheezcap_groups;
	}
}
